<?php

namespace TrendyolApi\TrendyolSpApi\Api;

use TrendyolApi\TrendyolSpApi\Exceptions\TrendyolApiException;

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
    public function getCategories(): array
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
    public function getCategoryById(int $category_id): array
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
    public function getCategoryAttributes(int $category_id): array
    {
        $endpoint = $this->endpoint_path . '/' . $category_id . '/attributes';
        
        return $this->request('GET', $endpoint);
    }
} 