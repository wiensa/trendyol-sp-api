<?php

namespace Serkan\TrendyolSpApi\Api;

use GuzzleHttp\Client;
use Illuminate\Config\Repository;
use Serkan\TrendyolSpApi\Exceptions\TrendyolApiException;

abstract class BaseApi
{
    /**
     * HTTP istemcisi
     */
    protected Client $client;

    /**
     * Yapılandırma deposu
     */
    protected Repository $config;

    /**
     * Trendyol API için tedarikçi kimliği
     */
    protected string $supplier_id;

    /**
     * API endpoint'inin yolu
     */
    protected string $endpoint_path;

    /**
     * BaseApi yapıcı.
     *
     * @param Client $client HTTP istemcisi
     * @param Repository $config Yapılandırma deposu
     * @param string $supplier_id Tedarikçi kimliği
     */
    public function __construct(Client $client, Repository $config, string $supplier_id)
    {
        $this->client = $client;
        $this->config = $config;
        $this->supplier_id = $supplier_id;
    }

    /**
     * API isteği gönderir.
     *
     * @param string $method HTTP metodu
     * @param string $endpoint API endpoint
     * @param array $options Guzzle options
     * @return array API yanıtı
     * @throws TrendyolApiException Bir API hatası oluştuğunda
     */
    protected function request(string $method, string $endpoint, array $options = []): array
    {
        try {
            $response = $this->client->request($method, $endpoint, $options);
            $status_code = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);
            
            if ($status_code >= 400) {
                throw new TrendyolApiException(
                    $data['errors'][0]['message'] ?? 'API error',
                    $status_code
                );
            }
            
            return $data;
        } catch (\Exception $e) {
            throw new TrendyolApiException(
                'API request error: ' . $e->getMessage(),
                $e->getCode() ?: 500
            );
        }
    }

    /**
     * Query parametreleri için filtre parametrelerini hazırlar.
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