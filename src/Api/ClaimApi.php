<?php

namespace TrendyolApi\TrendyolSpApi\Api;

use TrendyolApi\TrendyolSpApi\Exceptions\TrendyolApiException;

class ClaimApi extends BaseApi
{
    /**
     * API endpoint'inin yolu
     */
    protected string $endpoint_path = '/suppliers/%s/claims';
    
    /**
     * Talepleri listeler.
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
     * Talep detayını getirir.
     *
     * @param int $claim_id Talep ID
     * @return array
     * @throws TrendyolApiException
     */
    public function get(int $claim_id): array
    {
        $endpoint = sprintf($this->endpoint_path . '/%s', $this->supplier_id, $claim_id);
        
        return $this->request('GET', $endpoint);
    }
    
    /**
     * Talebe not ekler.
     *
     * @param int $claim_id Talep ID
     * @param string $note Not içeriği
     * @return array
     * @throws TrendyolApiException
     */
    public function addNote(int $claim_id, string $note): array
    {
        $endpoint = sprintf($this->endpoint_path . '/%s/notes', $this->supplier_id, $claim_id);
        
        return $this->request('POST', $endpoint, [
            'json' => [
                'text' => $note,
            ],
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }
    
    /**
     * Talep durumunu günceller.
     *
     * @param int $claim_id Talep ID
     * @param string $status Yeni durum (örn: "ACCEPTED", "REJECTED", "SOLVED")
     * @param string|null $reason Güncelleme nedeni/açıklaması (opsiyonel)
     * @return array
     * @throws TrendyolApiException
     */
    public function updateStatus(int $claim_id, string $status, ?string $reason = null): array
    {
        $endpoint = sprintf($this->endpoint_path . '/%s/status', $this->supplier_id, $claim_id);
        
        $data = [
            'status' => $status
        ];
        
        if ($reason !== null) {
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
     * Talebe döküman/delil yükler.
     *
     * @param int $claim_id Talep ID
     * @param string $file_content Base64 formatında dosya içeriği
     * @param string $file_name Dosya adı
     * @return array
     * @throws TrendyolApiException
     */
    public function uploadDocument(int $claim_id, string $file_content, string $file_name): array
    {
        $endpoint = sprintf($this->endpoint_path . '/%s/documents', $this->supplier_id, $claim_id);
        
        return $this->request('POST', $endpoint, [
            'json' => [
                'fileContent' => $file_content,
                'fileName' => $file_name,
            ],
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }
} 