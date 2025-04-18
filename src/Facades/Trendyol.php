<?php

namespace Serkan\TrendyolSpApi\Facades;

use Illuminate\Support\Facades\Facade;
use Serkan\TrendyolSpApi\Api\ProductApi;
use Serkan\TrendyolSpApi\Api\OrderApi;
use Serkan\TrendyolSpApi\Api\CategoryApi;
use Serkan\TrendyolSpApi\Api\BrandApi;
use Serkan\TrendyolSpApi\Api\SupplierAddressApi;

/**
 * @method static ProductApi products()
 * @method static OrderApi orders()
 * @method static CategoryApi categories()
 * @method static BrandApi brands()
 * @method static SupplierAddressApi supplierAddresses()
 * @method static array request(string $method, string $endpoint, array $options = [])
 * 
 * @see \Serkan\TrendyolSpApi\Trendyol
 */
class Trendyol extends Facade
{
    /**
     * Facade'in erişim noktasını döndürür.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'trendyol';
    }
} 