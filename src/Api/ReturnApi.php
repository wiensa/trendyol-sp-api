<?php

namespace TrendyolApi\TrendyolSpApi\Api;

use TrendyolApi\TrendyolSpApi\Exceptions\TrendyolApiException;

class ReturnApi extends BaseApi
{
    /**
     * API endpoint'inin yolu
     */
    protected string $endpoint_path = '/suppliers/%s/returns';
    
    /**
     * İade listesini getirir.
     *
     * @param array $filter Filtre parametreleri
     * @return array
     * @throws TrendyolApiException
     */
    public function list(array $filter = []): array
    {
        $endpoint = sprintf($this->endpoint_path, $this->supplier_id);
        $query_params = $this->prepareQueryParams($filter);
        
        return $this->request('GET', $this->buildUri($endpoint, $query_params));
    }
    
    /**
     * İade detayını getirir.
     *
     * @param int $return_id İade ID
     * @return array
     * @throws TrendyolApiException
     */
    public function get(int $return_id): array
    {
        $endpoint = sprintf($this->endpoint_path . '/%s', $this->supplier_id, $return_id);
        
        return $this->request('GET', $endpoint);
    }
    
    /**
     * İade paketinin durumunu günceller.
     *
     * @param int $return_package_id İade paket ID
     * @param string $status Yeni durum (örn: "APPROVED" veya "REJECTED")
     * @param string $reason İade ret nedeni (sadece REJECTED durumunda)
     * @return array
     * @throws TrendyolApiException
     */
    public function updateStatus(int $return_package_id, string $status, string $reason = ''): array
    {
        $endpoint = sprintf('/suppliers/%s/returns/%s', $this->supplier_id, $return_package_id);
        
        $data = [
            'status' => $status
        ];
        
        if ($status === 'REJECTED' && !empty($reason)) {
            $data['reason'] = $reason;
        }
        
        return $this->request('PUT', $endpoint, [
            'json' => $data,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }
    
    /**
     * İade kargo takip numarasını günceller.
     *
     * @param int $return_package_id İade paket ID
     * @param string $tracking_number Kargo takip numarası
     * @return array
     * @throws TrendyolApiException
     */
    public function updateTrackingNumber(int $return_package_id, string $tracking_number): array
    {
        $endpoint = sprintf('/suppliers/%s/returns/%s/tracking-number', $this->supplier_id, $return_package_id);
        
        return $this->request('PUT', $endpoint, [
            'json' => [
                'trackingNumber' => $tracking_number,
            ],
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }
} 