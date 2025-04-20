<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Config\Repository;
use TrendyolApi\TrendyolSpApi\Trendyol;
use TrendyolApi\TrendyolSpApi\Api\ProductApi;
use TrendyolApi\TrendyolSpApi\Api\OrderApi;
use TrendyolApi\TrendyolSpApi\Api\CategoryApi;
use TrendyolApi\TrendyolSpApi\Api\BrandApi;
use TrendyolApi\TrendyolSpApi\Api\SupplierAddressApi;
use TrendyolApi\TrendyolSpApi\Api\ClaimApi;
use TrendyolApi\TrendyolSpApi\Api\ReturnApi;
use TrendyolApi\TrendyolSpApi\Api\CustomerQuestionApi;
use TrendyolApi\TrendyolSpApi\Api\ShipmentProviderApi;
use TrendyolApi\TrendyolSpApi\Exceptions\TrendyolApiException;
use TrendyolApi\TrendyolSpApi\Services\ProductService;
use TrendyolApi\TrendyolSpApi\Services\OrderService;
use TrendyolApi\TrendyolSpApi\Services\CategoryService;
use TrendyolApi\TrendyolSpApi\Services\ClaimService;

test('Trendyol sınıfı API nesnelerini doğru şekilde oluşturur', function () {
    // Test konfigürasyonu
    $config = new Repository([
        'trendyol' => [
            'credentials' => [
                'supplier_id' => 'test-supplier-id',
                'api_key' => 'test-api-key',
                'api_secret' => 'test-api-secret',
            ],
            'rate_limit' => [
                'enabled' => false,
                'max_requests_per_second' => 5,
            ],
            'base_url' => 'https://api.trendyol.com/sapigw/',
            'request' => [
                'timeout' => 30,
                'connect_timeout' => 5,
            ],
            'debug' => false,
        ],
    ]);

    // Trendyol nesnesini oluştur
    $trendyol = new Trendyol($config);

    // API nesnelerinin doğru sınıflardan oluşturulduğunu kontrol et
    expect($trendyol->products())->toBeInstanceOf(ProductService::class);
    expect($trendyol->orders())->toBeInstanceOf(OrderService::class);
    expect($trendyol->categories())->toBeInstanceOf(CategoryService::class);
    expect($trendyol->brands())->toBeInstanceOf(BrandApi::class);
    expect($trendyol->supplierAddresses())->toBeInstanceOf(SupplierAddressApi::class);
    expect($trendyol->claims())->toBeInstanceOf(ClaimService::class);
    expect($trendyol->returns())->toBeInstanceOf(ReturnApi::class);
    expect($trendyol->customerQuestions())->toBeInstanceOf(CustomerQuestionApi::class);
    expect($trendyol->shipmentProviders())->toBeInstanceOf(ShipmentProviderApi::class);
});

test('Trendyol sınıfı API isteği gönderirken doğru yanıt döndürür', function () {
    // Mock HTTP yanıtı
    $mock = new MockHandler([
        new Response(200, [], json_encode([
            'status' => 'success',
            'data' => ['test' => 'data'],
        ])),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository([
        'trendyol' => [
            'credentials' => [
                'supplier_id' => 'test-supplier-id',
                'api_key' => 'test-api-key',
                'api_secret' => 'test-api-secret',
            ],
            'rate_limit' => [
                'enabled' => false,
                'max_requests_per_second' => 5,
            ],
            'cache' => [
                'enabled' => false,
            ],
            'debug' => false,
        ],
    ]);

    // Reflection kullanarak private client özelliğini değiştir
    $trendyol = new Trendyol($config);
    $reflection = new ReflectionClass($trendyol);
    $property = $reflection->getProperty('client');
    $property->setAccessible(true);
    $property->setValue($trendyol, $client);
    
    // http_client özelliğini de değiştir
    $http_client_property = $reflection->getProperty('http_client');
    $http_client_property->setAccessible(true);
    $http_client_property->setValue($trendyol, $client);

    // İstek gönder ve yanıtı kontrol et
    $response = $trendyol->request('GET', '/test-endpoint');
    
    expect($response)->toBe([
        'status' => 'success',
        'data' => ['test' => 'data'],
    ]);
});

test('Trendyol sınıfı API hatası döndürdüğünde exception fırlatır', function () {
    // Mock HTTP yanıtı (400 Bad Request)
    $mock = new MockHandler([
        new Response(400, [], json_encode([
            'errors' => [
                [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Test validation error',
                ],
            ],
        ])),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository([
        'trendyol' => [
            'credentials' => [
                'supplier_id' => 'test-supplier-id',
                'api_key' => 'test-api-key',
                'api_secret' => 'test-api-secret',
            ],
            'rate_limit' => [
                'enabled' => false,
                'max_requests_per_second' => 5,
            ],
            'cache' => [
                'enabled' => false,
            ],
            'debug' => false,
        ],
    ]);

    // Reflection kullanarak private client özelliğini değiştir
    $trendyol = new Trendyol($config);
    $reflection = new ReflectionClass($trendyol);
    $property = $reflection->getProperty('client');
    $property->setAccessible(true);
    $property->setValue($trendyol, $client);
    
    // http_client özelliğini de değiştir
    $http_client_property = $reflection->getProperty('http_client');
    $http_client_property->setAccessible(true);
    $http_client_property->setValue($trendyol, $client);

    // TrendyolApiException bekle
    $exception = null;
    try {
        $trendyol->request('GET', '/test-endpoint');
    } catch (TrendyolApiException $e) {
        $exception = $e;
    }
    
    expect($exception)->toBeInstanceOf(TrendyolApiException::class);
    expect($exception->getMessage())->toContain('Test validation error');
}); 