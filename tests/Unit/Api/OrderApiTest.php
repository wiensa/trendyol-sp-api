<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Config\Repository;
use TrendyolApi\TrendyolSpApi\Api\OrderApi;
use TrendyolApi\TrendyolSpApi\Exceptions\TrendyolApiException;

test('OrderApi->list metodu doğru şekilde istek gönderir ve yanıt döndürür', function () {
    // Mock HTTP yanıtı
    $orders_response = [
        'totalElements' => 2,
        'totalPages' => 1,
        'content' => [
            [
                'id' => 1,
                'orderNumber' => 'TSO1234567',
                'status' => 'Created',
                'orderDate' => '2023-01-01T10:00:00',
                'lines' => [],
            ],
            [
                'id' => 2,
                'orderNumber' => 'TSO7654321',
                'status' => 'Picking',
                'orderDate' => '2023-01-02T11:00:00',
                'lines' => [],
            ],
        ],
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($orders_response)),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // OrderApi nesnesini oluştur
    $order_api = new OrderApi($client, $config, $supplier_id);

    // Siparişleri listele
    $response = $order_api->list(['startDate' => '2023-01-01', 'endDate' => '2023-01-03', 'status' => 'Created']);
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($orders_response);
});

test('OrderApi->get metodu belirli bir siparişi getirir', function () {
    // Mock HTTP yanıtı
    $order_response = [
        'id' => 1,
        'orderNumber' => 'TSO1234567',
        'status' => 'Created',
        'orderDate' => '2023-01-01T10:00:00',
        'customerId' => 12345,
        'cargoTrackingNumber' => 'CT123456789',
        'cargoProviderName' => 'Test Kargo',
        'shipmentAddress' => [
            'address1' => 'Test Adres 1',
            'city' => 'İstanbul',
            'district' => 'Kadıköy',
        ],
        'totalPrice' => 199.99,
        'lines' => [
            [
                'lineId' => 101,
                'productId' => 1001,
                'barcode' => '8680000000001',
                'quantity' => 2,
                'price' => 99.99,
            ],
        ],
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($order_response)),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // OrderApi nesnesini oluştur
    $order_api = new OrderApi($client, $config, $supplier_id);

    // Sipariş detayı getir
    $response = $order_api->get(1);
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($order_response);
});

test('OrderApi->updatePackageStatus metodu sipariş durumunu günceller', function () {
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

    // OrderApi nesnesini oluştur
    $order_api = new OrderApi($client, $config, $supplier_id);

    // Sipariş satırlarını hazırla
    $lines = [
        'lines' => [
            [
                'lineId' => 101,
                'status' => 'Picking',
            ],
        ],
    ];

    // Sipariş durumunu güncelle
    $response = $order_api->updatePackageStatus(12345, $lines);
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($update_response);
});

test('OrderApi->cancelPackage metodu sipariş paketini iptal eder', function () {
    // Mock HTTP yanıtı
    $cancel_response = [
        'status' => 'success',
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($cancel_response)),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // OrderApi nesnesini oluştur
    $order_api = new OrderApi($client, $config, $supplier_id);

    // Sipariş paketini iptal et
    $response = $order_api->cancelPackage(12345, 'Müşteri talebi üzerine iptal edildi');
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($cancel_response);
});

test('OrderApi->updateTrackingNumber metodu takip numarasını günceller', function () {
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

    // OrderApi nesnesini oluştur
    $order_api = new OrderApi($client, $config, $supplier_id);

    // Takip numarasını güncelle
    $response = $order_api->updateTrackingNumber(12345, 'TRK987654321');
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($update_response);
});

test('OrderApi->sendInvoiceLink metodu fatura bilgisi gönderir', function () {
    // Mock HTTP yanıtı
    $invoice_response = [
        'status' => 'success',
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($invoice_response)),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // OrderApi nesnesini oluştur
    $order_api = new OrderApi($client, $config, $supplier_id);

    // Fatura bilgisi gönder
    $response = $order_api->sendInvoiceLink(12345, 'INV12345', '2023-01-15');
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($invoice_response);
}); 