<?php

namespace Serkan\TrendyolSpApi\Api;

use Serkan\TrendyolSpApi\Exceptions\TrendyolApiException;

class ProductApi extends BaseApi
{
    /**
     * API endpoint'inin yolu
     */
    protected string $endpoint_path = '/suppliers/%s/products';
    
    /**
     * Ürünleri listeler.
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
     * Ürün detayı getirir.
     *
     * @param int $product_id Ürün ID
     * @return array
     * @throws TrendyolApiException
     */
    public function get(int $product_id): array
    {
        $endpoint = sprintf($this->endpoint_path . '/%s', $this->supplier_id, $product_id);
        
        return $this->request('GET', $endpoint);
    }
    
    /**
     * Ürün oluşturur.
     *
     * @param array $product_data Ürün verileri
     * @return array
     * @throws TrendyolApiException
     */
    public function create(array $product_data): array
    {
        $endpoint = sprintf($this->endpoint_path, $this->supplier_id);
        
        return $this->request('POST', $endpoint, [
            'json' => ['items' => [$product_data]],
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }
    
    /**
     * Toplu ürün oluşturur.
     *
     * @param array $products Ürün verileri dizisi
     * @return array
     * @throws TrendyolApiException
     */
    public function createBatch(array $products): array
    {
        $endpoint = sprintf($this->endpoint_path, $this->supplier_id);
        
        return $this->request('POST', $endpoint, [
            'json' => ['items' => $products],
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }
    
    /**
     * Ürün günceller.
     *
     * @param array $product_data Ürün verileri
     * @return array
     * @throws TrendyolApiException
     */
    public function update(array $product_data): array
    {
        $endpoint = sprintf($this->endpoint_path, $this->supplier_id);
        
        return $this->request('PUT', $endpoint, [
            'json' => ['items' => [$product_data]],
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }
    
    /**
     * Ürün stok ve fiyat bilgisini günceller.
     *
     * @param string $barcode Barkod
     * @param int $quantity Stok miktarı
     * @param float $price Fiyat
     * @param float|null $sale_price İndirimli fiyat (opsiyonel)
     * @return array
     * @throws TrendyolApiException
     */
    public function updatePriceAndStock(string $barcode, int $quantity, float $price, ?float $sale_price = null): array
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
        
        return $this->request('POST', $endpoint, [
            'json' => $data,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }
    
    /**
     * Toplu ürün günceller.
     *
     * @param array $products Ürün verileri dizisi
     * @return array
     * @throws TrendyolApiException
     */
    public function updateBatch(array $products): array
    {
        $endpoint = sprintf($this->endpoint_path, $this->supplier_id);
        
        return $this->request('PUT', $endpoint, [
            'json' => ['items' => $products],
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }
    
    /**
     * Ürün siler.
     *
     * @param string $barcode Barkod
     * @return array
     * @throws TrendyolApiException
     */
    public function delete(string $barcode): array
    {
        $endpoint = sprintf($this->endpoint_path . '?barcode=%s', $this->supplier_id, $barcode);
        
        return $this->request('DELETE', $endpoint);
    }
} 