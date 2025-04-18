<?php

namespace TrendyolApi\TrendyolSpApi\Facades;

use Illuminate\Support\Facades\Facade;
use TrendyolApi\TrendyolSpApi\Api\ProductApi;
use TrendyolApi\TrendyolSpApi\Api\OrderApi;
use TrendyolApi\TrendyolSpApi\Api\CategoryApi;
use TrendyolApi\TrendyolSpApi\Api\BrandApi;
use TrendyolApi\TrendyolSpApi\Api\SupplierAddressApi;

/**
 * @method static ProductApi products()
 * @method static OrderApi orders()
 * @method static CategoryApi categories()
 * @method static BrandApi brands()
 * @method static SupplierAddressApi supplierAddresses()
 * @method static array request(string $method, string $endpoint, array $options = [])
 * 
 * @see \TrendyolApi\TrendyolSpApi\Trendyol
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