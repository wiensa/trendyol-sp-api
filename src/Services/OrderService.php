<?php

namespace TrendyolApi\TrendyolSpApi\Services;

use GuzzleHttp\Client;
use Illuminate\Config\Repository;
use TrendyolApi\TrendyolSpApi\Exceptions\TrendyolApiException;

class OrderService extends BaseService
{
    /**
     * API endpoint'inin yolu
     */
    protected string $endpoint_path = '/suppliers/%s/orders';
    
    /**
     * OrderService constructor.
     *
     * @param Client $client HTTP istemcisi
     * @param Repository $config Yapılandırma deposu
     * @param string $supplier_id Tedarikçi kimliği
     */
    public function __construct(Client $client, Repository $config, string $supplier_id)
    {
        parent::__construct($client, $config, $supplier_id);
        
        $this->service_name = 'OrderService';
    }
    
    /**
     * Siparişleri listeler.
     *
     * @param array $filter Filtre parametreleri
     * @return array|null
     * @throws TrendyolApiException
     */
    public function getOrders(array $filter = []): ?array
    {
        $endpoint = sprintf($this->endpoint_path, $this->supplier_id);
        $query_params = $this->prepareQueryParams($filter);
        
        $response = $this->get($this->buildUri($endpoint, $query_params));
        return $this->formatPaginatedResponse($response, 'content');
    }
    
    /**
     * Sipariş detaylarını getirir.
     *
     * @param int $order_id Sipariş ID
     * @return array|null
     * @throws TrendyolApiException
     */
    public function getOrderDetail(int $order_id): ?array
    {
        $endpoint = sprintf('/suppliers/%s/orders/%s', $this->supplier_id, $order_id);
        
        $response = $this->get($endpoint);
        return $this->formatSingleResponse($response);
    }
    
    /**
     * Sipariş satır detaylarını getirir.
     *
     * @param int $line_id Sipariş satır ID
     * @return array|null
     * @throws TrendyolApiException
     */
    public function getOrderLine(int $line_id): ?array
    {
        $endpoint = sprintf('/suppliers/%s/orders/shipment-packages/%s', $this->supplier_id, $line_id);
        
        $response = $this->get($endpoint);
        return $this->formatSingleResponse($response);
    }

    /**
     * Siparişi onaylar.
     *
     * @param int $shipment_package_id Kargo paket ID
     * @param array $lines Sipariş satırları
     * @return array|null
     * @throws TrendyolApiException
     */
    public function acceptOrderItems(int $shipment_package_id, array $lines): ?array
    {
        $endpoint = sprintf('/suppliers/%s/orders/shipment-packages/%s', $this->supplier_id, $shipment_package_id);

        $response = $this->put($endpoint, $lines, [], [
            'Content-Type' => 'application/json',
        ]);
        
        return $this->formatSingleResponse($response);
    }
    
    /**
     * Sipariş paketini iptal eder.
     *
     * @param int $shipment_package_id Kargo paket ID
     * @param string $reason İptal nedeni
     * @return array|null
     * @throws TrendyolApiException
     */
    public function cancelOrder(int $shipment_package_id, string $reason): ?array
    {
        $endpoint = sprintf('/suppliers/%s/orders/shipment-packages/%s', $this->supplier_id, $shipment_package_id);
        
        $response = $this->delete($endpoint, [
            'reason' => $reason,
        ], [], [
            'Content-Type' => 'application/json',
        ]);
        
        return $this->formatSingleResponse($response);
    }
    
    /**
     * Sipariş için takip numarası günceller.
     *
     * @param int $shipment_package_id Kargo paket ID
     * @param string $tracking_number Takip numarası
     * @return array|null
     * @throws TrendyolApiException
     */
    public function updateTrackingNumber(int $shipment_package_id, string $tracking_number): ?array
    {
        $endpoint = sprintf('/suppliers/%s/orders/shipment-packages/%s/update-tracking-number', 
            $this->supplier_id, 
            $shipment_package_id
        );
        
        $response = $this->put($endpoint, [
            'trackingNumber' => $tracking_number,
        ], [], [
            'Content-Type' => 'application/json',
        ]);
        
        return $this->formatSingleResponse($response);
    }
    
    /**
     * Fatura bilgisi gönderir.
     *
     * @param int $shipment_package_id Kargo paket ID
     * @param string $invoice_number Fatura numarası
     * @param string $invoice_date Fatura tarihi (YYYY-MM-DD)
     * @return array|null
     * @throws TrendyolApiException
     */
    public function sendInvoiceLink(int $shipment_package_id, string $invoice_number, string $invoice_date): ?array
    {
        $endpoint = sprintf('/suppliers/%s/orders/shipment-packages/%s/invoice-link',
            $this->supplier_id,
            $shipment_package_id
        );
        
        $response = $this->post($endpoint, [
            'invoiceNumber' => $invoice_number,
            'invoiceDate' => $invoice_date,
        ], [], [
            'Content-Type' => 'application/json',
        ]);
        
        return $this->formatSingleResponse($response);
    }

    /**
     * Fatura dosyasını gönderir.
     *
     * @param int $shipment_package_id Kargo paket ID
     * @param string $invoice_file Base64 formatında fatura dosyası
     * @return array|null
     * @throws TrendyolApiException
     */
    public function sendInvoiceFile(int $shipment_package_id, string $invoice_file): ?array
    {
        $endpoint = sprintf('/suppliers/%s/orders/shipment-packages/%s/invoice-file',
            $this->supplier_id,
            $shipment_package_id
        );
        
        $response = $this->post($endpoint, [
            'invoiceContent' => $invoice_file,
        ], [], [
            'Content-Type' => 'application/json',
        ]);
        
        return $this->formatSingleResponse($response);
    }
    
    /**
     * Sipariş kalemleri kargolama işlemini gerçekleştirir.
     * 
     * @param array $shipping_data Kargolama verileri
     * @return array|null
     * @throws TrendyolApiException
     */
    public function shipOrderItems(array $shipping_data): ?array
    {
        $endpoint = sprintf('/suppliers/%s/orders/update-tracking-number', $this->supplier_id);
        
        $response = $this->post($endpoint, $shipping_data, [], [
            'Content-Type' => 'application/json',
        ]);
        
        return $this->formatSingleResponse($response);
    }
} 