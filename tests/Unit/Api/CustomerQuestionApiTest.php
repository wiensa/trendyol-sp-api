<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Config\Repository;
use TrendyolApi\TrendyolSpApi\Api\CustomerQuestionApi;
use TrendyolApi\TrendyolSpApi\Exceptions\TrendyolApiException;

test('CustomerQuestionApi->list metodu doğru şekilde istek gönderir ve yanıt döndürür', function () {
    // Mock HTTP yanıtı
    $questions_response = [
        'totalElements' => 2,
        'totalPages' => 1,
        'content' => [
            [
                'id' => 1,
                'questionId' => 'Q1234567',
                'status' => 'WAITING_FOR_ANSWER',
                'creationDate' => '2023-01-01T10:00:00',
                'customerId' => 12345,
                'productId' => 67890,
                'text' => 'Bu ürün ne zaman kargoya verilir?',
            ],
            [
                'id' => 2,
                'questionId' => 'Q7654321',
                'status' => 'ANSWERED',
                'creationDate' => '2023-01-02T11:00:00',
                'customerId' => 54321,
                'productId' => 98765,
                'text' => 'Ürün renkleri görseldeki gibi mi?',
            ],
        ],
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($questions_response)),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // CustomerQuestionApi nesnesini oluştur
    $question_api = new CustomerQuestionApi($client, $config, $supplier_id);

    // Soruları listele
    $response = $question_api->list(['startDate' => '2023-01-01', 'endDate' => '2023-01-03', 'status' => 'WAITING_FOR_ANSWER']);
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($questions_response);
});

test('CustomerQuestionApi->get metodu belirli bir soruyu getirir', function () {
    // Mock HTTP yanıtı
    $question_response = [
        'id' => 1,
        'questionId' => 'Q1234567',
        'status' => 'WAITING_FOR_ANSWER',
        'creationDate' => '2023-01-01T10:00:00',
        'customerId' => 12345,
        'productId' => 67890,
        'text' => 'Bu ürün ne zaman kargoya verilir?',
        'answers' => [],
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($question_response)),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // CustomerQuestionApi nesnesini oluştur
    $question_api = new CustomerQuestionApi($client, $config, $supplier_id);

    // Soru detayını getir
    $response = $question_api->get(1);
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($question_response);
});

test('CustomerQuestionApi->answer metodu soruyu yanıtlar', function () {
    // Mock HTTP yanıtı
    $answer_response = [
        'status' => 'success',
        'answerId' => 101,
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($answer_response)),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // CustomerQuestionApi nesnesini oluştur
    $question_api = new CustomerQuestionApi($client, $config, $supplier_id);

    // Soruyu yanıtla
    $response = $question_api->answer(1, 'Ürün 2 gün içerisinde kargoya verilecektir.');
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($answer_response);
});

test('CustomerQuestionApi->escalate metodu soruyu eskalasyon yapar', function () {
    // Mock HTTP yanıtı
    $escalate_response = [
        'status' => 'success',
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($escalate_response)),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // CustomerQuestionApi nesnesini oluştur
    $question_api = new CustomerQuestionApi($client, $config, $supplier_id);

    // Soruyu eskalasyon yap
    $response = $question_api->escalate(1, 'Bu soru bizim ürünümüzle ilgili değil.');
    
    // Beklenen yanıtı kontrol et
    expect($response)->toBe($escalate_response);
});

test('CustomerQuestionApi API hatası döndürdüğünde exception fırlatır', function () {
    // Mock HTTP yanıtı (400 Bad Request)
    $mock = new MockHandler([
        new Response(400, [], json_encode([
            'errors' => [
                [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Geçersiz soru yanıtı',
                ],
            ],
        ])),
    ]);

    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);

    // Test konfigürasyonu
    $config = new Repository();
    $supplier_id = 'test-supplier-id';

    // CustomerQuestionApi nesnesini oluştur
    $question_api = new CustomerQuestionApi($client, $config, $supplier_id);

    // TrendyolApiException bekle - exception değerlerini kontrol et
    try {
        $question_api->answer(1, '');
        fail('Exception bekleniyor');
    } catch (TrendyolApiException $e) {
        expect($e)->toBeInstanceOf(TrendyolApiException::class);
        
        // HTTP 400 olmalı
        expect($e->getCode())->toBe(400);
    }
}); 