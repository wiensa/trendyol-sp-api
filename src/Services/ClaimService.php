<?php

namespace TrendyolApi\TrendyolSpApi\Services;

use GuzzleHttp\Client;
use Illuminate\Config\Repository;
use TrendyolApi\TrendyolSpApi\Exceptions\TrendyolApiException;

class ClaimService extends BaseService
{
    /**
     * API endpoint'inin yolu
     */
    protected string $endpoint_path = '/suppliers/%s/claims';
    
    /**
     * ClaimService constructor.
     *
     * @param Client $client HTTP istemcisi
     * @param Repository $config Yapılandırma deposu
     * @param string $supplier_id Tedarikçi kimliği
     */
    public function __construct(Client $client, Repository $config, string $supplier_id)
    {
        parent::__construct($client, $config, $supplier_id);
        
        $this->service_name = 'ClaimService';
    }
    
    /**
     * Tüm talepleri (şikayetleri) listeler.
     *
     * @param array $filter Filtre parametreleri
     * @return array|null
     * @throws TrendyolApiException
     */
    public function getClaims(array $filter = []): ?array
    {
        $endpoint = sprintf($this->endpoint_path, $this->supplier_id);
        $query_params = $this->prepareQueryParams($filter);
        
        $response = $this->get($this->buildUri($endpoint, $query_params));
        return $this->formatPaginatedResponse($response, 'content');
    }
    
    /**
     * Talep (şikayet) detaylarını getirir.
     *
     * @param int $claim_id Talep ID
     * @return array|null
     * @throws TrendyolApiException
     */
    public function getClaimDetail(int $claim_id): ?array
    {
        $endpoint = sprintf($this->endpoint_path . '/%s', $this->supplier_id, $claim_id);
        
        $response = $this->get($endpoint);
        return $this->formatSingleResponse($response);
    }
    
    /**
     * Talebe (şikayete) yanıt gönderir.
     *
     * @param int $claim_id Talep ID
     * @param string $message Yanıt mesajı
     * @param array $attachments Ekler (opsiyonel)
     * @return array|null
     * @throws TrendyolApiException
     */
    public function replyToClaim(int $claim_id, string $message, array $attachments = []): ?array
    {
        $endpoint = sprintf($this->endpoint_path . '/%s/messages', $this->supplier_id, $claim_id);
        
        $data = [
            'message' => $message,
        ];
        
        if (!empty($attachments)) {
            $data['attachments'] = $attachments;
        }
        
        $response = $this->post($endpoint, $data, [], [
            'Content-Type' => 'application/json',
        ]);
        
        return $this->formatSingleResponse($response);
    }
    
    /**
     * Talep (şikayet) mesajlarını listeler.
     *
     * @param int $claim_id Talep ID
     * @return array|null
     * @throws TrendyolApiException
     */
    public function getClaimMessages(int $claim_id): ?array
    {
        $endpoint = sprintf($this->endpoint_path . '/%s/messages', $this->supplier_id, $claim_id);
        
        $response = $this->get($endpoint);
        return $this->formatPaginatedResponse($response, 'messages');
    }
    
    /**
     * Talep (şikayet) için işlem yapar.
     *
     * @param int $claim_id Talep ID
     * @param string $action İşlem (örn: "accept", "reject")
     * @param string|null $reason Sebep (opsiyonel)
     * @return array|null
     * @throws TrendyolApiException
     */
    public function performClaimAction(int $claim_id, string $action, ?string $reason = null): ?array
    {
        $endpoint = sprintf($this->endpoint_path . '/%s/actions', $this->supplier_id, $claim_id);
        
        $data = [
            'action' => $action,
        ];
        
        if ($reason !== null) {
            $data['reason'] = $reason;
        }
        
        $response = $this->post($endpoint, $data, [], [
            'Content-Type' => 'application/json',
        ]);
        
        return $this->formatSingleResponse($response);
    }
} 