<?php

namespace TrendyolApi\TrendyolSpApi\Tests\Feature;

use TrendyolApi\TrendyolSpApi\Facades\Trendyol;
use TrendyolApi\TrendyolSpApi\Trendyol as TrendyolClass;

test('service provider Trendyol sınıfını başarıyla kaydeder', function () {
    // Facade aracılığıyla Trendyol sınıfına erişilebilir olmalı
    expect(Trendyol::getFacadeRoot())->toBeInstanceOf(TrendyolClass::class);
});

test('service provider config dosyasını doğru şekilde yükler', function () {
    // Konfigürasyonun başarıyla yüklendiğini doğrula
    expect(config('trendyol'))->toBeArray();
    expect(config('trendyol.credentials'))->toBeArray();
    expect(config('trendyol.base_url'))->not->toBeNull();
    expect(config('trendyol.request'))->toBeArray();
    expect(config('trendyol.cache'))->toBeArray();
    expect(config('trendyol.rate_limit'))->toBeArray();
    
    // Test ortamı için ayarlanmış değerlerin doğruluğunu kontrol et
    expect(config('trendyol.credentials.supplier_id'))->toBe(env('TRENDYOL_SUPPLIER_ID', 'test-supplier-id'));
    expect(config('trendyol.credentials.api_key'))->toBe(env('TRENDYOL_API_KEY', 'test-api-key'));
    expect(config('trendyol.credentials.api_secret'))->toBe(env('TRENDYOL_API_SECRET', 'test-api-secret'));
});

test('Trendyol instance singleton olarak container\'a kaydedilir', function () {
    // Aynı Trendyol örneğine erişildiğini kontrol et
    $instance1 = app('trendyol');
    $instance2 = app('trendyol');
    
    expect($instance1)->toBe($instance2);
});

test('Trendyol facade ile erişilen nesne singleton instance ile aynıdır', function () {
    // Facade ve container'dan alınan örneğin aynı olduğunu kontrol et
    $facade_instance = Trendyol::getFacadeRoot();
    $app_instance = app('trendyol');
    
    expect($facade_instance)->toBe($app_instance);
}); 