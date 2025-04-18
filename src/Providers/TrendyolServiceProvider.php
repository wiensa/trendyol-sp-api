<?php

namespace Serkan\TrendyolSpApi\Providers;

use Illuminate\Support\ServiceProvider;
use Serkan\TrendyolSpApi\Trendyol;

class TrendyolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap paket hizmetleri.
     */
    public function boot(): void
    {
        $this->publishConfig();
    }

    /**
     * Paket hizmetlerini kaydeder.
     */
    public function register(): void
    {
        $this->registerConfig();
        $this->registerTrendyol();
    }

    /**
     * Yapılandırma dosyasını yayınlar.
     */
    private function publishConfig(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                dirname(__DIR__, 2).'/config/trendyol.php' => config_path('trendyol.php'),
            ], 'config');
        }
    }

    /**
     * Yapılandırma dosyasını kaydeder.
     */
    private function registerConfig(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__, 2).'/config/trendyol.php', 'trendyol');
    }

    /**
     * Trendyol sınıfını container'a kaydeder.
     */
    private function registerTrendyol(): void
    {
        $this->app->singleton('trendyol', function ($app) {
            return new Trendyol($app['config']);
        });

        $this->app->alias('trendyol', Trendyol::class);
    }
} 