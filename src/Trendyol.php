<?php

namespace Serkan\TrendyolSpApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Serkan\TrendyolSpApi\Api\ProductApi;
use Serkan\TrendyolSpApi\Api\OrderApi;
use Serkan\TrendyolSpApi\Api\CategoryApi;
use Serkan\TrendyolSpApi\Api\BrandApi;
use Serkan\TrendyolSpApi\Api\SupplierAddressApi;
use Serkan\TrendyolSpApi\Exceptions\TrendyolApiException;
use Serkan\TrendyolSpApi\Support\RateLimiter;

class Trendyol
{
    /**
     * HTTP istemcisi
     */
    protected Client $client;

    /**
     * Yapılandırma deposu
     */
    protected Repository $config;

    /**
     * Ürün API'si
     */
    protected ?ProductApi $product_api = null;

    /**
     * Sipariş API'si
     */
    protected ?OrderApi $order_api = null;

    /**
     * Kategori API'si
     */
    protected ?CategoryApi $category_api = null;

    /**
     * Marka API'si
     */
    protected ?BrandApi $brand_api = null;

    /**
     * Tedarikçi Adresi API'si
     */
    protected ?SupplierAddressApi $supplier_address_api = null;

    /**
     * Rate Limiter
     */
    protected RateLimiter $rate_limiter;

    /**
     * Trendyol API için tedarikçi kimliği
     */
    protected string $supplier_id;

    /**
     * Trendyol API için API anahtarı
     */
    protected string $api_key;

    /**
     * Trendyol API için API sırrı
     */
    protected string $api_secret;

    /**
     * Trendyol sınıfı yapıcı.
     *
     * @param Repository $config Yapılandırma deposu
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
        $this->supplier_id = $config->get('trendyol.credentials.supplier_id');
        $this->api_key = $config->get('trendyol.credentials.api_key');
        $this->api_secret = $config->get('trendyol.credentials.api_secret');
        $this->rate_limiter = new RateLimiter(
            $config->get('trendyol.rate_limit.enabled'),
            $config->get('trendyol.rate_limit.max_requests_per_second')
        );

        $this->initializeHttpClient();
    }

    /**
     * HTTP istemcisini başlatır.
     *
     * @return void
     */
    protected function initializeHttpClient(): void
    {
        $handler = HandlerStack::create();

        // Rate limit middleware
        $handler->push(Middleware::mapRequest(function (RequestInterface $request) {
            if ($this->config->get('trendyol.rate_limit.enabled')) {
                $this->rate_limiter->throttle();
            }
            return $request;
        }));

        // Auth header middleware
        $handler->push(Middleware::mapRequest(function (RequestInterface $request) {
            return $request->withHeader(
                'Authorization',
                'Basic ' . base64_encode($this->api_key . ':' . $this->api_secret)
            );
        }));

        // Logging middleware (debug mod açıksa)
        if ($this->config->get('trendyol.debug')) {
            $handler->push(Middleware::tap(function (RequestInterface $request) {
                Log::debug('Trendyol API Request', [
                    'method' => $request->getMethod(),
                    'uri' => (string) $request->getUri(),
                    'headers' => $request->getHeaders(),
                    'body' => (string) $request->getBody(),
                ]);
            }, function (RequestInterface $request, $options, ResponseInterface $response) {
                Log::debug('Trendyol API Response', [
                    'status' => $response->getStatusCode(),
                    'reason' => $response->getReasonPhrase(),
                    'headers' => $response->getHeaders(),
                    'body' => (string) $response->getBody(),
                ]);
            }));
        }

        $this->client = new Client([
            'base_uri' => $this->config->get('trendyol.base_url'),
            'handler' => $handler,
            'timeout' => $this->config->get('trendyol.request.timeout'),
            'connect_timeout' => $this->config->get('trendyol.request.connect_timeout'),
            'http_errors' => false,
        ]);
    }

    /**
     * ProductApi örneğini döndürür.
     *
     * @return ProductApi
     */
    public function products(): ProductApi
    {
        if ($this->product_api === null) {
            $this->product_api = new ProductApi($this->client, $this->config, $this->supplier_id);
        }
        
        return $this->product_api;
    }

    /**
     * OrderApi örneğini döndürür.
     *
     * @return OrderApi
     */
    public function orders(): OrderApi
    {
        if ($this->order_api === null) {
            $this->order_api = new OrderApi($this->client, $this->config, $this->supplier_id);
        }
        
        return $this->order_api;
    }

    /**
     * CategoryApi örneğini döndürür.
     *
     * @return CategoryApi
     */
    public function categories(): CategoryApi
    {
        if ($this->category_api === null) {
            $this->category_api = new CategoryApi($this->client, $this->config, $this->supplier_id);
        }
        
        return $this->category_api;
    }

    /**
     * BrandApi örneğini döndürür.
     *
     * @return BrandApi
     */
    public function brands(): BrandApi
    {
        if ($this->brand_api === null) {
            $this->brand_api = new BrandApi($this->client, $this->config, $this->supplier_id);
        }
        
        return $this->brand_api;
    }

    /**
     * SupplierAddressApi örneğini döndürür.
     *
     * @return SupplierAddressApi
     */
    public function supplierAddresses(): SupplierAddressApi
    {
        if ($this->supplier_address_api === null) {
            $this->supplier_address_api = new SupplierAddressApi($this->client, $this->config, $this->supplier_id);
        }
        
        return $this->supplier_address_api;
    }

    /**
     * API isteği gönderir.
     *
     * @param string $method HTTP metodu
     * @param string $endpoint API endpoint
     * @param array $options Guzzle options
     * @return array API yanıtı
     * @throws TrendyolApiException Bir API hatası oluştuğunda
     */
    public function request(string $method, string $endpoint, array $options = []): array
    {
        $cache_key = null;
        
        // Cache kontrolü sadece GET istekleri için yapılır
        if ($method === 'GET' && $this->config->get('trendyol.cache.enabled')) {
            $cache_key = $this->generateCacheKey($method, $endpoint, $options);
            
            if (Cache::has($cache_key)) {
                return Cache::get($cache_key);
            }
        }
        
        try {
            $response = $this->client->request($method, $endpoint, $options);
            $status_code = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);
            
            if ($status_code >= 400) {
                throw new TrendyolApiException(
                    $data['errors'][0]['message'] ?? 'API error',
                    $status_code
                );
            }
            
            // GET isteği başarılıysa ve cache aktifse, sonucu cache'e kaydet
            if ($method === 'GET' && $cache_key && $this->config->get('trendyol.cache.enabled')) {
                Cache::put(
                    $cache_key, 
                    $data, 
                    $this->config->get('trendyol.cache.ttl')
                );
            }
            
            return $data;
        } catch (GuzzleException $e) {
            throw new TrendyolApiException(
                'HTTP request error: ' . $e->getMessage(),
                $e->getCode()
            );
        }
    }

    /**
     * Cache key oluşturur.
     *
     * @param string $method HTTP metodu
     * @param string $endpoint API endpoint
     * @param array $options Guzzle options
     * @return string Cache key
     */
    protected function generateCacheKey(string $method, string $endpoint, array $options): string
    {
        $prefix = $this->config->get('trendyol.cache.prefix');
        $key_parts = [
            $method,
            $endpoint,
            md5(json_encode($options))
        ];
        
        return $prefix . implode('_', $key_parts);
    }
} 