<?php

namespace Serkan\TrendyolSpApi\Api;

use Serkan\TrendyolSpApi\Exceptions\TrendyolApiException;

class CategoryApi extends BaseApi
{
    /**
     * API endpoint'inin yolu
     */
    protected string $endpoint_path = '/product-categories';
    
    /**
     * Tüm kategorileri getirir.
     *
     * @return array
     * @throws TrendyolApiException
     */
    public function list(): array
    {
        return $this->request('GET', $this->endpoint_path);
    }
    
    /**
     * Kategori detaylarını getirir.
     *
     * @param int $category_id Kategori ID
     * @return array
     * @throws TrendyolApiException
     */
    public function get(int $category_id): array
    {
        $endpoint = $this->endpoint_path . '/' . $category_id;
        
        return $this->request('GET', $endpoint);
    }
    
    /**
     * Kategori özelliklerini getirir.
     *
     * @param int $category_id Kategori ID
     * @return array
     * @throws TrendyolApiException
     */
    public function getAttributes(int $category_id): array
    {
        $endpoint = $this->endpoint_path . '/' . $category_id . '/attributes';
        
        return $this->request('GET', $endpoint);
    }
} 