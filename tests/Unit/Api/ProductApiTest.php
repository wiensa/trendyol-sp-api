<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Config\Repository;
use TrendyolApi\TrendyolSpApi\Api\ProductApi;
use TrendyolApi\TrendyolSpApi\Exceptions\TrendyolApiException;

test('ProductApi->getProducts metodu doğru şekilde istek gönderir ve yanıt döndürür', function () {
    // Mock HTTP yanıtı
    $products_response = [
        'totalElements' => 2,
        'totalPages' => 1,
        'content' => [
            [
                'id' => 1,
                'barcode' => '8680000000001',
                'title' => 'Test Ürün 1',
            ],
            [
                'id' => 2,
                'barcode' => '8680000000002',
                'title' => 'Test Ürün 2',
            ],
        ],
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($products_response)),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // ProductApi nesnesini oluştur
    $product_api = new ProductApi($client, $config, $supplier_id);

    // Ürünleri listele
    $response = $product_api->getProducts(['page' => 0, 'size' => 25]);
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($products_response);
});

test('ProductApi->getProductById metodu belirli bir ürünü getirir', function () {
    // Mock HTTP yanıtı
    $product_response = [
        'id' => 1,
        'barcode' => '8680000000001',
        'title' => 'Test Ürün 1',
        'description' => 'Bu bir test ürünüdür',
        'categoryId' => 123,
        'brandId' => 456,
        'price' => 99.99,
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($product_response)),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // ProductApi nesnesini oluştur
    $product_api = new ProductApi($client, $config, $supplier_id);

    // Ürün detayı getir
    $response = $product_api->getProductById(1);
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($product_response);
});

test('ProductApi->createProduct metodu yeni ürün oluşturur', function () {
    // Mock HTTP yanıtı
    $create_response = [
        'batchRequestId' => '12345',
        'status' => 'SUCCESS',
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($create_response)),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // ProductApi nesnesini oluştur
    $product_api = new ProductApi($client, $config, $supplier_id);

    // Test ürün verisi
    $product_data = [
        'barcode' => '8680000000001',
        'title' => 'Test Ürün 1',
        'productMainId' => 'TRY001',
        'brandId' => 456,
        'categoryId' => 123,
        'stockAmount' => 100,
        'listPrice' => 149.99,
        'salePrice' => 99.99,
    ];

    // Ürün oluştur
    $response = $product_api->createProduct($product_data);
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($create_response);
});

test('ProductApi->updatePriceAndStock metodu ürün stok ve fiyat bilgisini günceller', function () {
    // Mock HTTP yanıtı
    $update_response = [
        'batchRequestId' => '12345',
        'status' => 'SUCCESS',
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($update_response)),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // ProductApi nesnesini oluştur
    $product_api = new ProductApi($client, $config, $supplier_id);

    // Fiyat ve stok güncelle
    $response = $product_api->updatePriceAndStock(
        '8680000000001',
        50,
        99.99,
        149.99
    );
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($update_response);
});

test('ProductApi->deleteProduct metodu ürün siler', function () {
    // Mock HTTP yanıtı
    $delete_response = [
        'batchRequestId' => '12345',
        'status' => 'SUCCESS',
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($delete_response)),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // ProductApi nesnesini oluştur
    $product_api = new ProductApi($client, $config, $supplier_id);

    // Ürün sil
    $response = $product_api->deleteProduct('8680000000001');
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($delete_response);
});

test('ProductApi API hatası döndürdüğünde exception fırlatır', function () {
    // Mock HTTP yanıtı (400 Bad Request)
    $mock = new MockHandler([
        new Response(400, [], json_encode([
            'errors' => [
                [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Invalid product data',
                ],
            ],
        ])),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // ProductApi nesnesini oluştur
    $product_api = new ProductApi($client, $config, $supplier_id);

    // TrendyolApiException bekle
    $exception = null;
    try {
        $product_api->createProduct([]);
    } catch (TrendyolApiException $e) {
        $exception = $e;
    }
    
    expect($exception)->toBeInstanceOf(TrendyolApiException::class);
    expect($exception->getMessage())->toContain('Invalid product data');
}); 