<?php

namespace TrendyolApi\TrendyolSpApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use TrendyolApi\TrendyolSpApi\Services\ProductService;
use TrendyolApi\TrendyolSpApi\Services\OrderService;
use TrendyolApi\TrendyolSpApi\Services\CategoryService;
use TrendyolApi\TrendyolSpApi\Services\ClaimService;
use TrendyolApi\TrendyolSpApi\Exceptions\TrendyolApiException;
use TrendyolApi\TrendyolSpApi\Support\RateLimiter;
use TrendyolApi\TrendyolSpApi\Traits\ApiRequest;
use TrendyolApi\TrendyolSpApi\Api\ReturnApi;
use TrendyolApi\TrendyolSpApi\Api\CustomerQuestionApi;
use TrendyolApi\TrendyolSpApi\Api\ShipmentProviderApi;
use TrendyolApi\TrendyolSpApi\Api\BrandApi;
use TrendyolApi\TrendyolSpApi\Api\SupplierAddressApi;

class Trendyol
{
    use ApiRequest;
    
    /**
     * HTTP istemcisi
     */
    protected Client $client;
    
    /**
     * ApiRequest trait'i için HTTP istemcisi
     */
    protected Client $http_client;

    /**
     * Yapılandırma deposu
     */
    protected Repository $config;

    /**
     * Ürün servisi
     */
    protected ?ProductService $product_service = null;

    /**
     * Sipariş servisi
     */
    protected ?OrderService $order_service = null;

    /**
     * Kategori servisi
     */
    protected ?CategoryService $category_service = null;
    
    /**
     * Talep/Şikayet servisi
     */
    protected ?ClaimService $claim_service = null;
    
    /**
     * İade API sınıfı
     */
    protected ?ReturnApi $return_api = null;
    
    /**
     * Müşteri Soruları API sınıfı
     */
    protected ?CustomerQuestionApi $customer_question_api = null;
    
    /**
     * Kargo Firmaları API sınıfı
     */
    protected ?ShipmentProviderApi $shipment_provider_api = null;
    
    /**
     * Marka API sınıfı
     */
    protected ?BrandApi $brand_api = null;
    
    /**
     * Tedarikçi Adresleri API sınıfı
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
        
        // ApiRequest trait'i için aynı istemciyi ata
        $this->http_client = $this->client;
    }

    /**
     * ProductService örneğini döndürür.
     *
     * @return ProductService
     */
    public function products(): ProductService
    {
        if ($this->product_service === null) {
            $this->product_service = new ProductService($this->client, $this->config, $this->supplier_id);
        }
        
        return $this->product_service;
    }

    /**
     * OrderService örneğini döndürür.
     *
     * @return OrderService
     */
    public function orders(): OrderService
    {
        if ($this->order_service === null) {
            $this->order_service = new OrderService($this->client, $this->config, $this->supplier_id);
        }
        
        return $this->order_service;
    }

    /**
     * CategoryService örneğini döndürür.
     *
     * @return CategoryService
     */
    public function categories(): CategoryService
    {
        if ($this->category_service === null) {
            $this->category_service = new CategoryService($this->client, $this->config, $this->supplier_id);
        }
        
        return $this->category_service;
    }

    /**
     * ClaimService örneğini döndürür.
     *
     * @return ClaimService
     */
    public function claims(): ClaimService
    {
        if ($this->claim_service === null) {
            $this->claim_service = new ClaimService($this->client, $this->config, $this->supplier_id);
        }
        
        return $this->claim_service;
    }
    
    /**
     * ReturnApi örneğini döndürür.
     *
     * @return ReturnApi
     */
    public function returns(): ReturnApi
    {
        if ($this->return_api === null) {
            $this->return_api = new ReturnApi($this->client, $this->config, $this->supplier_id);
        }
        
        return $this->return_api;
    }
    
    /**
     * CustomerQuestionApi örneğini döndürür.
     *
     * @return CustomerQuestionApi
     */
    public function customerQuestions(): CustomerQuestionApi
    {
        if ($this->customer_question_api === null) {
            $this->customer_question_api = new CustomerQuestionApi($this->client, $this->config, $this->supplier_id);
        }
        
        return $this->customer_question_api;
    }
    
    /**
     * ShipmentProviderApi örneğini döndürür.
     *
     * @return ShipmentProviderApi
     */
    public function shipmentProviders(): ShipmentProviderApi
    {
        if ($this->shipment_provider_api === null) {
            $this->shipment_provider_api = new ShipmentProviderApi($this->client, $this->config, $this->supplier_id);
        }
        
        return $this->shipment_provider_api;
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
     * HTTP istemcisini döndürür.
     *
     * @return Client
     */
    public function getHttpClient(): Client
    {
        return $this->client;
    }
    
    /**
     * Tedarikçi ID'sini döndürür.
     *
     * @return string
     */
    public function getSupplierId(): string
    {
        return $this->supplier_id;
    }
} 