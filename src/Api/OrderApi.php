<?php

namespace TrendyolApi\TrendyolSpApi\Api;

use TrendyolApi\TrendyolSpApi\Exceptions\TrendyolApiException;

class OrderApi extends BaseApi
{
    /**
     * API endpoint'inin yolu
     */
    protected string $endpoint_path = '/suppliers/%s/orders';
    
    /**
     * Siparişleri listeler.
     *
     * @param array $filter Filtre parametreleri
     * @return array
     * @throws TrendyolApiException
     */
    public function getOrders(array $filter = []): array
    {
        $endpoint = sprintf($this->endpoint_path, $this->supplier_id);
        $query_params = $this->prepareQueryParams($filter);
        
        return $this->request('GET', $this->buildUri($endpoint, $query_params));
    }
    
    /**
     * Sipariş detaylarını getirir.
     *
     * @param int $order_id Sipariş ID
     * @return array
     * @throws TrendyolApiException
     */
    public function getOrderDetail(int $order_id): array
    {
        $endpoint = sprintf('/suppliers/%s/orders/%s', $this->supplier_id, $order_id);
        
        return $this->request('GET', $endpoint);
    }
    
    /**
     * Sipariş satır detaylarını getirir.
     *
     * @param int $line_id Sipariş satır ID
     * @return array
     * @throws TrendyolApiException
     */
    public function getOrderLine(int $line_id): array
    {
        $endpoint = sprintf('/suppliers/%s/orders/shipment-packages/%s', $this->supplier_id, $line_id);
        
        return $this->request('GET', $endpoint);
    }

    /**
     * Siparişi onaylar.
     *
     * @param int $shipment_package_id Kargo paket ID
     * @param array $lines Sipariş satırları
     * @return array
     * @throws TrendyolApiException
     */
    public function acceptOrderItems(int $shipment_package_id, array $lines): array
    {
        $endpoint = sprintf('/suppliers/%s/orders/shipment-packages/%s', $this->supplier_id, $shipment_package_id);

        return $this->request('PUT', $endpoint, [
            'json' => $lines,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }
    
    /**
     * Sipariş paketini iptal eder.
     *
     * @param int $shipment_package_id Kargo paket ID
     * @param string $reason İptal nedeni
     * @return array
     * @throws TrendyolApiException
     */
    public function cancelOrder(int $shipment_package_id, string $reason): array
    {
        $endpoint = sprintf('/suppliers/%s/orders/shipment-packages/%s', $this->supplier_id, $shipment_package_id);
        
        return $this->request('DELETE', $endpoint, [
            'json' => [
                'reason' => $reason,
            ],
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }
    
    /**
     * Sipariş için takip numarası günceller.
     *
     * @param int $shipment_package_id Kargo paket ID
     * @param string $tracking_number Takip numarası
     * @return array
     * @throws TrendyolApiException
     */
    public function updateTrackingNumber(int $shipment_package_id, string $tracking_number): array
    {
        $endpoint = sprintf('/suppliers/%s/orders/shipment-packages/%s/update-tracking-number', 
            $this->supplier_id, 
            $shipment_package_id
        );
        
        return $this->request('PUT', $endpoint, [
            'json' => [
                'trackingNumber' => $tracking_number,
            ],
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }
    
    /**
     * Fatura bilgisi gönderir.
     *
     * @param int $shipment_package_id Kargo paket ID
     * @param string $invoice_number Fatura numarası
     * @param string $invoice_date Fatura tarihi (YYYY-MM-DD)
     * @return array
     * @throws TrendyolApiException
     */
    public function sendInvoiceLink(int $shipment_package_id, string $invoice_number, string $invoice_date): array
    {
        $endpoint = sprintf('/suppliers/%s/orders/shipment-packages/%s/invoice-link',
            $this->supplier_id,
            $shipment_package_id
        );
        
        return $this->request('POST', $endpoint, [
            'json' => [
                'invoiceNumber' => $invoice_number,
                'invoiceDate' => $invoice_date,
            ],
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Fatura dosyasını gönderir.
     *
     * @param int $shipment_package_id Kargo paket ID
     * @param string $invoice_file Base64 formatında fatura dosyası
     * @return array
     * @throws TrendyolApiException
     */
    public function sendInvoiceFile(int $shipment_package_id, string $invoice_file): array
    {
        $endpoint = sprintf('/suppliers/%s/orders/shipment-packages/%s/invoice-file',
            $this->supplier_id,
            $shipment_package_id
        );
        
        return $this->request('POST', $endpoint, [
            'json' => [
                'invoiceContent' => $invoice_file,
            ],
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }
} 