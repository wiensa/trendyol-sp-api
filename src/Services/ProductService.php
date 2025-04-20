<?php

namespace TrendyolApi\TrendyolSpApi\Services;

use GuzzleHttp\Client;
use Illuminate\Config\Repository;
use TrendyolApi\TrendyolSpApi\Exceptions\TrendyolApiException;

class ProductService extends BaseService
{
    /**
     * API endpoint'inin yolu
     */
    protected string $endpoint_path = '/suppliers/%s/products';
    
    /**
     * ProductService constructor.
     *
     * @param Client $client HTTP istemcisi
     * @param Repository $config Yapılandırma deposu
     * @param string $supplier_id Tedarikçi kimliği
     */
    public function __construct(Client $client, Repository $config, string $supplier_id)
    {
        parent::__construct($client, $config, $supplier_id);
        
        $this->service_name = 'ProductService';
    }
    
    /**
     * Ürünleri listeler.
     *
     * @param array $filter Filtre parametreleri
     * @return array|null
     * @throws TrendyolApiException
     */
    public function getProducts(array $filter = []): ?array
    {
        $endpoint = sprintf($this->endpoint_path, $this->supplier_id);
        $query_params = $this->prepareQueryParams($filter);
        
        $response = $this->get($this->buildUri($endpoint, $query_params));
        return $this->formatPaginatedResponse($response, 'content');
    }
    
    /**
     * Ürün detayı getirir.
     *
     * @param int $product_id Ürün ID
     * @return array|null
     * @throws TrendyolApiException
     */
    public function getProductById(int $product_id): ?array
    {
        $endpoint = sprintf($this->endpoint_path . '/%s', $this->supplier_id, $product_id);
        
        $response = $this->get($endpoint);
        return $this->formatSingleResponse($response);
    }
    
    /**
     * Ürün oluşturur.
     *
     * @param array $product_data Ürün verileri
     * @return array|null
     * @throws TrendyolApiException
     */
    public function createProduct(array $product_data): ?array
    {
        $endpoint = sprintf($this->endpoint_path, $this->supplier_id);
        
        $response = $this->post($endpoint, ['items' => [$product_data]], [], [
            'Content-Type' => 'application/json',
        ]);
        
        return $this->formatSingleResponse($response);
    }
    
    /**
     * Toplu ürün oluşturur.
     *
     * @param array $products Ürün verileri dizisi
     * @return array|null
     * @throws TrendyolApiException
     */
    public function batchCreateProducts(array $products): ?array
    {
        $endpoint = sprintf($this->endpoint_path, $this->supplier_id);
        
        $response = $this->post($endpoint, ['items' => $products], [], [
            'Content-Type' => 'application/json',
        ]);
        
        return $this->formatSingleResponse($response);
    }
    
    /**
     * Ürün günceller.
     *
     * @param array $product_data Ürün verileri
     * @return array|null
     * @throws TrendyolApiException
     */
    public function updateProduct(array $product_data): ?array
    {
        $endpoint = sprintf($this->endpoint_path, $this->supplier_id);
        
        $response = $this->put($endpoint, ['items' => [$product_data]], [], [
            'Content-Type' => 'application/json',
        ]);
        
        return $this->formatSingleResponse($response);
    }
    
    /**
     * Ürün stok ve fiyat bilgisini günceller.
     *
     * @param string $barcode Barkod
     * @param int $quantity Stok miktarı
     * @param float $price Fiyat
     * @param float|null $sale_price İndirimli fiyat (opsiyonel)
     * @return array|null
     * @throws TrendyolApiException
     */
    public function updatePriceAndStock(string $barcode, int $quantity, float $price, ?float $sale_price = null): ?array
    {
        $endpoint = sprintf('/suppliers/%s/products/price-and-inventory', $this->supplier_id);
        
        $data = [
            'items' => [
                [
                    'barcode' => $barcode,
                    'quantity' => $quantity,
                    'salePrice' => $price,
                ]
            ]
        ];
        
        // Add sale price if provided
        if ($sale_price !== null) {
            $data['items'][0]['listPrice'] = $sale_price;
        }
        
        $response = $this->post($endpoint, $data, [], [
            'Content-Type' => 'application/json',
        ]);
        
        return $this->formatSingleResponse($response);
    }
    
    /**
     * Toplu ürün günceller.
     *
     * @param array $products Ürün verileri dizisi
     * @return array|null
     * @throws TrendyolApiException
     */
    public function batchUpdateProducts(array $products): ?array
    {
        $endpoint = sprintf($this->endpoint_path, $this->supplier_id);
        
        $response = $this->put($endpoint, ['items' => $products], [], [
            'Content-Type' => 'application/json',
        ]);
        
        return $this->formatSingleResponse($response);
    }
    
    /**
     * Ürün siler.
     *
     * @param string $barcode Barkod
     * @return array|null
     * @throws TrendyolApiException
     */
    public function deleteProduct(string $barcode): ?array
    {
        $endpoint = sprintf($this->endpoint_path . '?barcode=%s', $this->supplier_id, $barcode);
        
        $response = $this->delete($endpoint);
        return $this->formatSingleResponse($response);
    }
} 