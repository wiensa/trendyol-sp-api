<?php

namespace TrendyolApi\TrendyolSpApi\Api;

use TrendyolApi\TrendyolSpApi\Exceptions\TrendyolApiException;

class ShipmentProviderApi extends BaseApi
{
    /**
     * API endpoint'inin yolu
     */
    protected string $endpoint_path = '/shipment-providers';
    
    /**
     * Kullanılabilir kargo firmalarını listeler.
     *
     * @return array
     * @throws TrendyolApiException
     */
    public function list(): array
    {
        return $this->request('GET', $this->endpoint_path);
    }
    
    /**
     * Tedarikçiye ait kargo hesaplarını listeler.
     *
     * @return array
     * @throws TrendyolApiException
     */
    public function getSupplierAccounts(): array
    {
        $endpoint = sprintf('/suppliers/%s/shipment-providers', $this->supplier_id);
        
        return $this->request('GET', $endpoint);
    }
    
    /**
     * Sevkiyat çıkışlarını listeler.
     *
     * @param array $filter Filtre parametreleri
     * @return array
     * @throws TrendyolApiException
     */
    public function getShipmentOutbounds(array $filter = []): array
    {
        $endpoint = sprintf('/suppliers/%s/shipment-outbounds', $this->supplier_id);
        $query_params = $this->prepareQueryParams($filter);
        
        return $this->request('GET', $this->buildUri($endpoint, $query_params));
    }
    
    /**
     * Sevkiyat paketi detayını getirir.
     *
     * @param int $shipment_outbound_id Sevkiyat çıkış ID
     * @return array
     * @throws TrendyolApiException
     */
    public function getShipmentOutbound(int $shipment_outbound_id): array
    {
        $endpoint = sprintf('/suppliers/%s/shipment-outbounds/%s', $this->supplier_id, $shipment_outbound_id);
        
        return $this->request('GET', $endpoint);
    }
    
    /**
     * Teslimat seçeneklerini getirir.
     *
     * @return array
     * @throws TrendyolApiException
     */
    public function getDeliveryOptions(): array
    {
        $endpoint = sprintf('/suppliers/%s/delivery-options', $this->supplier_id);
        
        return $this->request('GET', $endpoint);
    }
    
    /**
     * Kargo etiketini indirir.
     *
     * @param int $shipment_package_id Kargo paket ID
     * @return array
     * @throws TrendyolApiException
     */
    public function downloadShippingLabel(int $shipment_package_id): array
    {
        $endpoint = sprintf('/suppliers/%s/orders/shipment-packages/%s/shipping-label', 
            $this->supplier_id, 
            $shipment_package_id
        );
        
        return $this->request('GET', $endpoint);
    }
    
    /**
     * Sevkiyat paketleri için toplu olarak kargo etiketlerini indirir.
     *
     * @param array $shipment_package_ids Kargo paket ID'leri dizisi
     * @return array
     * @throws TrendyolApiException
     */
    public function downloadBulkShippingLabel(array $shipment_package_ids): array
    {
        $endpoint = sprintf('/suppliers/%s/orders/shipment-packages/shipping-labels', $this->supplier_id);
        
        return $this->request('POST', $endpoint, [
            'json' => [
                'shipmentPackageIds' => $shipment_package_ids,
            ],
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }
    
    /**
     * Sevkiyat çıkışı oluşturur.
     *
     * @param array $outbound_data Sevkiyat çıkış verileri
     * @return array
     * @throws TrendyolApiException
     */
    public function createShipmentOutbound(array $outbound_data): array
    {
        $endpoint = sprintf('/suppliers/%s/shipment-outbounds', $this->supplier_id);
        
        return $this->request('POST', $endpoint, [
            'json' => $outbound_data,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }
} 