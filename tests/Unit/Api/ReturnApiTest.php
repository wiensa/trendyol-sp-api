<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Config\Repository;
use TrendyolApi\TrendyolSpApi\Api\ReturnApi;
use TrendyolApi\TrendyolSpApi\Exceptions\TrendyolApiException;

test('ReturnApi->list metodu doğru şekilde istek gönderir ve yanıt döndürür', function () {
    // Mock HTTP yanıtı
    $returns_response = [
        'totalElements' => 2,
        'totalPages' => 1,
        'content' => [
            [
                'id' => 1,
                'returnPackageId' => 'RET1234567',
                'status' => 'PENDING',
                'creationDate' => '2023-01-01T10:00:00',
                'orderNumber' => 'TSO1234567',
                'items' => [],
            ],
            [
                'id' => 2,
                'returnPackageId' => 'RET7654321',
                'status' => 'APPROVED',
                'creationDate' => '2023-01-02T11:00:00',
                'orderNumber' => 'TSO7654321',
                'items' => [],
            ],
        ],
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($returns_response)),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // ReturnApi nesnesini oluştur
    $return_api = new ReturnApi($client, $config, $supplier_id);

    // İadeleri listele
    $response = $return_api->list(['startDate' => '2023-01-01', 'endDate' => '2023-01-03', 'status' => 'PENDING']);
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($returns_response);
});

test('ReturnApi->get metodu belirli bir iadeyi getirir', function () {
    // Mock HTTP yanıtı
    $return_response = [
        'id' => 1,
        'returnPackageId' => 'RET1234567',
        'status' => 'PENDING',
        'creationDate' => '2023-01-01T10:00:00',
        'orderNumber' => 'TSO1234567',
        'customerId' => 12345,
        'trackingNumber' => 'RT123456789',
        'items' => [
            [
                'id' => 101,
                'productId' => 1001,
                'barcode' => '8680000000001',
                'quantity' => 1,
                'reason' => 'Ürün beklediğim gibi değil',
            ],
        ],
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($return_response)),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // ReturnApi nesnesini oluştur
    $return_api = new ReturnApi($client, $config, $supplier_id);

    // İade detayı getir
    $response = $return_api->get(1);
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($return_response);
});

test('ReturnApi->updateStatus metodu iade durumunu günceller', function () {
    // Mock HTTP yanıtı
    $update_response = [
        'status' => 'success',
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($update_response)),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // ReturnApi nesnesini oluştur
    $return_api = new ReturnApi($client, $config, $supplier_id);

    // İade durumunu güncelle
    $response = $return_api->updateStatus(1, 'APPROVED');
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($update_response);
});

test('ReturnApi->updateStatus metodu iade durumunu REJECTED olarak günceller', function () {
    // Mock HTTP yanıtı
    $update_response = [
        'status' => 'success',
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($update_response)),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // ReturnApi nesnesini oluştur
    $return_api = new ReturnApi($client, $config, $supplier_id);

    // İade durumunu red olarak güncelle
    $response = $return_api->updateStatus(1, 'REJECTED', 'Ürün kullanılmış');
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($update_response);
});

test('ReturnApi->updateTrackingNumber metodu takip numarasını günceller', function () {
    // Mock HTTP yanıtı
    $update_response = [
        'status' => 'success',
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($update_response)),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // ReturnApi nesnesini oluştur
    $return_api = new ReturnApi($client, $config, $supplier_id);

    // Takip numarasını güncelle
    $response = $return_api->updateTrackingNumber(1, 'TRK987654321');
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($update_response);
});

test('ReturnApi API hatası döndürdüğünde exception fırlatır', function () {
    // Mock HTTP yanıtı (400 Bad Request)
    $mock = new MockHandler([
        new Response(400, [], json_encode([
            'errors' => [
                [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Geçersiz iade durumu',
                ],
            ],
        ])),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // ReturnApi nesnesini oluştur
    $return_api = new ReturnApi($client, $config, $supplier_id);

    // TrendyolApiException bekle - exception değerlerini kontrol et
    try {
        $return_api->updateStatus(1, 'INVALID_STATUS');
        fail('Exception bekleniyor');
    } catch (TrendyolApiException $e) {
        expect($e)->toBeInstanceOf(TrendyolApiException::class);
        
        // HTTP 400 olmalı
        expect($e->getCode())->toBe(400);
    }
}); 