<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport;

use RectorPrefix202401\Nette\Utils\Reflection;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Namespace_;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\CodingStyle\NodeAnalyzer\UseImportNameMatcher;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeTraverser;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\ValueObject\Application\File;
use ReflectionClass;
/**
 * @see \Rector\Tests\CodingStyle\ClassNameImport\ShortNameResolver\ShortNameResolverTest
 */
final class ShortNameResolver
{
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\CodingStyle\NodeAnalyzer\UseImportNameMatcher
     */
    private $useImportNameMatcher;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @var array<string, string[]>
     */
    private $shortNamesByFilePath = [];
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeNameResolver $nodeNameResolver, ReflectionProvider $reflectionProvider, BetterNodeFinder $betterNodeFinder, UseImportNameMatcher $useImportNameMatcher, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->useImportNameMatcher = $useImportNameMatcher;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    /**
     * @return array<string, string>
     */
    public function resolveFromFile(File $file) : array
    {
        $filePath = $file->getFilePath();
        if (isset($this->shortNamesByFilePath[$filePath])) {
            return $this->shortNamesByFilePath[$filePath];
        }
        $shortNamesToFullyQualifiedNames = $this->resolveForStmts($file->getNewStmts());
        $this->shortNamesByFilePath[$filePath] = $shortNamesToFullyQualifiedNames;
        return $shortNamesToFullyQualifiedNames;
    }
    /**
     * Collects all "class <SomeClass>", "trait <SomeTrait>" and "interface <SomeInterface>"
     * @return string[]
     */
    public function resolveShortClassLikeNames(File $file) : array
    {
        $newStmts = $file->getNewStmts();
        /** @var Namespace_[]|FileWithoutNamespace[] $namespaces */
        $namespaces = \array_filter($newStmts, static function (Stmt $stmt) : bool {
            return $stmt instanceof Namespace_ || $stmt instanceof FileWithoutNamespace;
        });
        if (\count($namespaces) !== 1) {
            // only handle single namespace nodes
            return [];
        }
        $namespace = \current($namespaces);
        /** @var ClassLike[] $classLikes */
        $classLikes = $this->betterNodeFinder->findInstanceOf($namespace->stmts, ClassLike::class);
        $shortClassLikeNames = [];
        foreach ($classLikes as $classLike) {
            $shortClassLikeNames[] = $this->nodeNameResolver->getShortName($classLike);
        }
        return \array_unique($shortClassLikeNames);
    }
    /**
     * @param Stmt[] $stmts
     * @return array<string, string>
     */
    private function resolveForStmts(array $stmts) : array
    {
        $shortNamesToFullyQualifiedNames = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmts, function (Node $node) use(&$shortNamesToFullyQualifiedNames) {
            // class name is used!
            if ($node instanceof ClassLike && $node->name instanceof Identifier) {
                $fullyQualifiedName = $this->nodeNameResolver->getName($node);
                if ($fullyQualifiedName === null) {
                    return null;
                }
                $shortNamesToFullyQualifiedNames[$node->name->toString()] = $fullyQualifiedName;
                return null;
            }
            if (!$node instanceof Name) {
                return null;
            }
            $originalName = $node->getAttribute(AttributeKey::ORIGINAL_NAME);
            if (!$originalName instanceof Name) {
                return null;
            }
            // already short
            if (\strpos($originalName->toString(), '\\') !== \false) {
                return null;
            }
            $shortNamesToFullyQualifiedNames[$originalName->toString()] = $this->nodeNameResolver->getName($node);
            return null;
        });
        $docBlockShortNamesToFullyQualifiedNames = $this->resolveFromStmtsDocBlocks($stmts);
        /** @var array<string, string> $result */
        $result = \array_merge($shortNamesToFullyQualifiedNames, $docBlockShortNamesToFullyQualifiedNames);
        return $result;
    }
    /**
     * @param Stmt[] $stmts
     * @return array<string, string>
     */
    private function resolveFromStmtsDocBlocks(array $stmts) : array
    {
        $classReflection = $this->resolveClassReflection($stmts);
        $shortNames = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmts, function (Node $node) use(&$shortNames) {
            // speed up for nodes that are
            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
            if (!$phpDocInfo instanceof PhpDocInfo) {
                return null;
            }
            $phpDocNodeTraverser = new PhpDocNodeTraverser();
            $phpDocNodeTraverser->traverseWithCallable($phpDocInfo->getPhpDocNode(), '', static function ($node) use(&$shortNames) {
                if ($node instanceof PhpDocTagNode) {
                    $shortName = \trim($node->name, '@');
                    if (\ucfirst($shortName) === $shortName) {
                        $shortNames[] = $shortName;
                    }
                    return null;
                }
                if ($node instanceof IdentifierTypeNode) {
                    $shortNames[] = $node->name;
                }
                return null;
            });
            return null;
        });
        return $this->fqnizeShortNames($shortNames, $classReflection, $stmts);
    }
    /**
     * @param Node[] $stmts
     */
    private function resolveClassReflection(array $stmts) : ?ClassReflection
    {
        $firstClassLike = $this->betterNodeFinder->findFirstInstanceOf($stmts, ClassLike::class);
        if (!$firstClassLike instanceof ClassLike) {
            return null;
        }
        $className = (string) $this->nodeNameResolver->getName($firstClassLike);
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        return $this->reflectionProvider->getClass($className);
    }
    /**
     * @param string[] $shortNames
     * @param Stmt[] $stmts
     * @return array<string, string>
     */
    private function fqnizeShortNames(array $shortNames, ?ClassReflection $classReflection, array $stmts) : array
    {
        $shortNamesToFullyQualifiedNames = [];
        $nativeReflectionClass = $classReflection instanceof ClassReflection && !$classReflection->isAnonymous() ? $classReflection->getNativeReflection() : null;
        foreach ($shortNames as $shortName) {
            $stmtsMatchedName = $this->useImportNameMatcher->matchNameWithStmts($shortName, $stmts);
            if ($nativeReflectionClass instanceof ReflectionClass) {
                $fullyQualifiedName = Reflection::expandClassName($shortName, $nativeReflectionClass);
            } elseif (\is_string($stmtsMatchedName)) {
                $fullyQualifiedName = $stmtsMatchedName;
            } else {
                $fullyQualifiedName = $shortName;
            }
            $shortNamesToFullyQualifiedNames[$shortName] = $fullyQualifiedName;
        }
        return $shortNamesToFullyQualifiedNames;
    }
}
