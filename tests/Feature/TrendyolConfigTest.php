<?php

namespace TrendyolApi\TrendyolSpApi\Tests\Feature;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Config;
use TrendyolApi\TrendyolSpApi\Trendyol;

test('publish komutu ile config dosyası yayınlanabilir', function () {
    $this->artisan('vendor:publish', [
        '--provider' => 'TrendyolApi\TrendyolSpApi\Providers\TrendyolServiceProvider',
        '--tag' => 'config',
    ])->assertExitCode(0);
    
    // Config dosyasının yayınlandığını kontrol et
    expect(file_exists(config_path('trendyol.php')))->toBeTrue();
});

test('config dosyası tüm gerekli alanları içerir', function () {
    // Gerekli config alanlarının varlığını kontrol et
    $config = require dirname(__DIR__, 2) . '/config/trendyol.php';
    
    expect($config)->toBeArray();
    expect($config)->toHaveKeys([
        'credentials',
        'base_url',
        'request',
        'cache',
        'debug',
        'rate_limit',
    ]);
    
    // Alt yapılandırma alanlarını kontrol et
    expect($config['credentials'])->toHaveKeys([
        'supplier_id',
        'api_key',
        'api_secret',
    ]);
    
    expect($config['request'])->toHaveKeys([
        'timeout',
        'connect_timeout',
        'retry_attempts',
        'retry_sleep',
    ]);
    
    expect($config['cache'])->toHaveKeys([
        'enabled',
        'ttl',
        'prefix',
    ]);
    
    expect($config['rate_limit'])->toHaveKeys([
        'enabled',
        'max_requests_per_second',
    ]);
});

test('farklı konfigürasyon değerleri ile Trendyol nesnesi oluşturulabilir', function () {
    // Özel konfigürasyon değerleri
    Config::set('trendyol.credentials.supplier_id', 'custom-supplier-id');
    Config::set('trendyol.credentials.api_key', 'custom-api-key');
    Config::set('trendyol.credentials.api_secret', 'custom-api-secret');
    Config::set('trendyol.debug', true);
    
    // Trendyol nesnesini oluştur
    $trendyol = app(Trendyol::class);
    
    // Reflection kullanarak private özellikleri kontrol et
    $reflection = new \ReflectionClass($trendyol);
    
    $supplier_id_property = $reflection->getProperty('supplier_id');
    $supplier_id_property->setAccessible(true);
    
    $api_key_property = $reflection->getProperty('api_key');
    $api_key_property->setAccessible(true);
    
    $api_secret_property = $reflection->getProperty('api_secret');
    $api_secret_property->setAccessible(true);
    
    // Konfigürasyon değerlerinin doğru şekilde atandığını kontrol et
    expect($supplier_id_property->getValue($trendyol))->toBe('custom-supplier-id');
    expect($api_key_property->getValue($trendyol))->toBe('custom-api-key');
    expect($api_secret_property->getValue($trendyol))->toBe('custom-api-secret');
}); 