<?php

namespace TrendyolApi\TrendyolSpApi\Services;

use GuzzleHttp\Client;
use Illuminate\Config\Repository;
use TrendyolApi\TrendyolSpApi\Exceptions\TrendyolApiException;

class CategoryService extends BaseService
{
    /**
     * API endpoint'inin yolu
     */
    protected string $endpoint_path = '/product-categories';
    
    /**
     * CategoryService constructor.
     *
     * @param Client $client HTTP istemcisi
     * @param Repository $config Yapılandırma deposu
     * @param string $supplier_id Tedarikçi kimliği
     */
    public function __construct(Client $client, Repository $config, string $supplier_id)
    {
        parent::__construct($client, $config, $supplier_id);
        
        $this->service_name = 'CategoryService';
    }
    
    /**
     * Tüm kategorileri getirir.
     *
     * @return array|null
     * @throws TrendyolApiException
     */
    public function getCategories(): ?array
    {
        $response = $this->get($this->endpoint_path);
        return $this->formatPaginatedResponse($response, 'categories');
    }
    
    /**
     * Kategori detaylarını getirir.
     *
     * @param int $category_id Kategori ID
     * @return array|null
     * @throws TrendyolApiException
     */
    public function getCategoryById(int $category_id): ?array
    {
        $endpoint = $this->endpoint_path . '/' . $category_id;
        $response = $this->get($endpoint);
        return $this->formatSingleResponse($response);
    }
    
    /**
     * Kategori özelliklerini getirir.
     *
     * @param int $category_id Kategori ID
     * @return array|null
     * @throws TrendyolApiException
     */
    public function getCategoryAttributes(int $category_id): ?array
    {
        $endpoint = $this->endpoint_path . '/' . $category_id . '/attributes';
        $response = $this->get($endpoint);
        return $this->formatPaginatedResponse($response, 'categoryAttributes');
    }
} 