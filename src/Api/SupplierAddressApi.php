<?php

namespace TrendyolApi\TrendyolSpApi\Api;

use TrendyolApi\TrendyolSpApi\Exceptions\TrendyolApiException;

class SupplierAddressApi extends BaseApi
{
    /**
     * API endpoint'inin yolu
     */
    protected string $endpoint_path = '/suppliers/%s/addresses';
    
    /**
     * Tedarikçi adreslerini listeler.
     *
     * @return array
     * @throws TrendyolApiException
     */
    public function list(): array
    {
        $endpoint = sprintf($this->endpoint_path, $this->supplier_id);
        
        return $this->request('GET', $endpoint);
    }
    
    /**
     * Yeni bir tedarikçi adresi ekler.
     *
     * @param array $address_data Adres verileri
     * @return array
     * @throws TrendyolApiException
     */
    public function create(array $address_data): array
    {
        $endpoint = sprintf($this->endpoint_path, $this->supplier_id);
        
        return $this->request('POST', $endpoint, [
            'json' => $address_data,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }
    
    /**
     * Tedarikçi adresini günceller.
     *
     * @param int $address_id Adres ID
     * @param array $address_data Adres verileri
     * @return array
     * @throws TrendyolApiException
     */
    public function update(int $address_id, array $address_data): array
    {
        $endpoint = sprintf($this->endpoint_path . '/%s', $this->supplier_id, $address_id);
        
        return $this->request('PUT', $endpoint, [
            'json' => $address_data,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }
    
    /**
     * Tedarikçi adresini siler.
     *
     * @param int $address_id Adres ID
     * @return array
     * @throws TrendyolApiException
     */
    public function delete(int $address_id): array
    {
        $endpoint = sprintf($this->endpoint_path . '/%s', $this->supplier_id, $address_id);
        
        return $this->request('DELETE', $endpoint);
    }
} 