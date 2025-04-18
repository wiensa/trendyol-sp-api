<?php

namespace Serkan\TrendyolSpApi\Api;

use Serkan\TrendyolSpApi\Exceptions\TrendyolApiException;

class BrandApi extends BaseApi
{
    /**
     * API endpoint'inin yolu
     */
    protected string $endpoint_path = '/brands';
    
    /**
     * Markaları listeler.
     *
     * @param array $filter Filtre parametreleri
     * @return array
     * @throws TrendyolApiException
     */
    public function list(array $filter = []): array
    {
        $query_params = array_merge(['page' => 0, 'size' => 100], $filter);
        
        return $this->request('GET', $this->buildUri($this->endpoint_path, $query_params));
    }
    
    /**
     * Belirli bir marka için arama yapar.
     *
     * @param string $name Marka adı
     * @return array
     * @throws TrendyolApiException
     */
    public function search(string $name): array
    {
        $query_params = ['name' => $name];
        
        return $this->request('GET', $this->buildUri($this->endpoint_path . '/by-name', $query_params));
    }
} 