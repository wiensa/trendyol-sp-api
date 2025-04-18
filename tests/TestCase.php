<?php

namespace TrendyolApi\TrendyolSpApi\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use TrendyolApi\TrendyolSpApi\Providers\TrendyolServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            TrendyolServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Test ortamında kullanılacak konfigürasyon ayarları
        $app['config']->set('trendyol-sp-api.supplier_id', env('TRENDYOL_SUPPLIER_ID', 'test-supplier-id'));
        $app['config']->set('trendyol-sp-api.api_key', env('TRENDYOL_API_KEY', 'test-api-key'));
        $app['config']->set('trendyol-sp-api.api_secret', env('TRENDYOL_API_SECRET', 'test-api-secret'));
        $app['config']->set('trendyol-sp-api.test_mode', env('TRENDYOL_API_TEST_MODE', true));
    }
} 