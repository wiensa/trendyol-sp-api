<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Config\Repository;
use TrendyolApi\TrendyolSpApi\Api\ClaimApi;
use TrendyolApi\TrendyolSpApi\Exceptions\TrendyolApiException;

test('ClaimApi->list metodu doğru şekilde istek gönderir ve yanıt döndürür', function () {
    // Mock HTTP yanıtı
    $claims_response = [
        'totalElements' => 2,
        'totalPages' => 1,
        'content' => [
            [
                'id' => 1,
                'claimNumber' => 'CLM1234567',
                'status' => 'PENDING',
                'creationDate' => '2023-01-01T10:00:00',
            ],
            [
                'id' => 2,
                'claimNumber' => 'CLM7654321',
                'status' => 'ACCEPTED',
                'creationDate' => '2023-01-02T11:00:00',
            ],
        ],
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($claims_response)),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // ClaimApi nesnesini oluştur
    $claim_api = new ClaimApi($client, $config, $supplier_id);

    // Talepleri listele
    $response = $claim_api->list(['startDate' => '2023-01-01', 'endDate' => '2023-01-03', 'status' => 'PENDING']);
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($claims_response);
});

test('ClaimApi->get metodu belirli bir talebi getirir', function () {
    // Mock HTTP yanıtı
    $claim_response = [
        'id' => 1,
        'claimNumber' => 'CLM1234567',
        'status' => 'PENDING',
        'creationDate' => '2023-01-01T10:00:00',
        'customerId' => 12345,
        'orderNumber' => 'TSO98765432',
        'description' => 'Ürün hasarlı geldi',
        'notes' => [
            [
                'id' => 101,
                'text' => 'Müşteri ile iletişime geçildi',
                'creationDate' => '2023-01-01T11:00:00',
            ],
        ],
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($claim_response)),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // ClaimApi nesnesini oluştur
    $claim_api = new ClaimApi($client, $config, $supplier_id);

    // Talep detayını getir
    $response = $claim_api->get(1);
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($claim_response);
});

test('ClaimApi->addNote metodu talebe not ekler', function () {
    // Mock HTTP yanıtı
    $note_response = [
        'status' => 'success',
        'noteId' => 102,
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($note_response)),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // ClaimApi nesnesini oluştur
    $claim_api = new ClaimApi($client, $config, $supplier_id);

    // Not ekle
    $response = $claim_api->addNote(1, 'Yeni not içeriği');
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($note_response);
});

test('ClaimApi->updateStatus metodu talep durumunu günceller', function () {
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

    // ClaimApi nesnesini oluştur
    $claim_api = new ClaimApi($client, $config, $supplier_id);

    // Durumu güncelle
    $response = $claim_api->updateStatus(1, 'ACCEPTED', 'Müşteri talebi haklı bulundu');
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($update_response);
});

test('ClaimApi->uploadDocument metodu talebe döküman yükler', function () {
    // Mock HTTP yanıtı
    $upload_response = [
        'status' => 'success',
        'documentId' => 201,
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($upload_response)),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // ClaimApi nesnesini oluştur
    $claim_api = new ClaimApi($client, $config, $supplier_id);

    // Döküman yükle
    $response = $claim_api->uploadDocument(1, 'base64_encoded_content', 'test_document.pdf');
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($upload_response);
});

test('ClaimApi API hatası döndürdüğünde exception fırlatır', function () {
    // Mock HTTP yanıtı (400 Bad Request)
    $mock = new MockHandler([
        new Response(400, [], json_encode([
            'errors' => [
                [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Geçersiz talep durumu',
                ],
            ],
        ])),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // ClaimApi nesnesini oluştur
    $claim_api = new ClaimApi($client, $config, $supplier_id);

    // TrendyolApiException bekle - exception değerlerini kontrol et
    try {
        $claim_api->updateStatus(1, 'INVALID_STATUS');
        fail('Exception bekleniyor');
    } catch (TrendyolApiException $e) {
        expect($e)->toBeInstanceOf(TrendyolApiException::class);
        
        // HTTP 400 olmalı
        expect($e->getCode())->toBe(400);
    }
}); 