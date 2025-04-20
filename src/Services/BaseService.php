<?php

namespace TrendyolApi\TrendyolSpApi\Services;

use GuzzleHttp\Client;
use Illuminate\Config\Repository;
use TrendyolApi\TrendyolSpApi\Traits\ApiRequest;
use TrendyolApi\TrendyolSpApi\Traits\ResponseFormatter;
use TrendyolApi\TrendyolSpApi\Exceptions\TrendyolApiException;

abstract class BaseService
{
    use ApiRequest, ResponseFormatter;

    /**
     * HTTP istemcisi
     */
    protected Client $http_client;

    /**
     * Yapılandırma deposu
     */
    protected Repository $config;

    /**
     * Trendyol API için tedarikçi kimliği
     */
    protected string $supplier_id;

    /**
     * Servis adı
     */
    protected string $service_name;

    /**
     * BaseService yapıcı.
     *
     * @param Client $client HTTP istemcisi
     * @param Repository $config Yapılandırma deposu
     * @param string $supplier_id Tedarikçi kimliği
     */
    public function __construct(Client $client, Repository $config, string $supplier_id)
    {
        $this->http_client = $client;
        $this->config = $config;
        $this->supplier_id = $supplier_id;
        $this->service_name = static::class;
    }

    /**
     * Sorgu parametreleri için filtre parametrelerini hazırlar.
     *
     * @param array $filter Filtreler
     * @return array API istekleri için sorgu parametreleri
     */
    protected function prepareQueryParams(array $filter = []): array
    {
        $params = [
            'supplierId' => $this->supplier_id,
        ];

        // Filtre parametrelerini ekle (varsa)
        return array_merge($params, $filter);
    }

    /**
     * Sorgu parametreleri içeren URI oluşturur.
     *
     * @param string $path Endpoint path
     * @param array $params Sorgu parametreleri
     * @return string Tam URI
     */
    protected function buildUri(string $path, array $params = []): string
    {
        if (empty($params)) {
            return $path;
        }

        return $path . '?' . http_build_query($params);
    }
} 