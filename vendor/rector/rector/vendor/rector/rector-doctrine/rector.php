<?php

declare (strict_types=1);
namespace RectorPrefix202401;

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->importNames();
    $rectorConfig->removeUnusedImports();
    $rectorConfig->paths([__DIR__ . '/src', __DIR__ . '/rules', __DIR__ . '/tests']);
    $rectorConfig->skip([
        // for tests
        '*/Source/*',
        '*/Fixture/*',
    ]);
    $rectorConfig->sets([LevelSetList::UP_TO_PHP_81, PHPUnitSetList::PHPUNIT_100, SetList::DEAD_CODE, SetList::CODE_QUALITY, SetList::CODING_STYLE, SetList::NAMING, SetList::TYPE_DECLARATION, SetList::PRIVATIZATION]);
    $rectorConfig->ruleWithConfiguration(StringClassNameToClassConstantRector::class, ['Doctrine\\*', 'Gedmo\\*', 'Knp\\*', 'DateTime', 'DateTimeInterface']);
};
