<?php

namespace TrendyolApi\TrendyolSpApi\Api;

use TrendyolApi\TrendyolSpApi\Exceptions\TrendyolApiException;

class CustomerQuestionApi extends BaseApi
{
    /**
     * API endpoint'inin yolu
     */
    protected string $endpoint_path = '/suppliers/%s/questions';
    
    /**
     * Müşteri sorularını listeler.
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
     * Müşteri sorusu detayını getirir.
     *
     * @param int $question_id Soru ID
     * @return array
     * @throws TrendyolApiException
     */
    public function get(int $question_id): array
    {
        $endpoint = sprintf($this->endpoint_path . '/%s', $this->supplier_id, $question_id);
        
        return $this->request('GET', $endpoint);
    }
    
    /**
     * Müşteri sorusunu yanıtlar.
     *
     * @param int $question_id Soru ID
     * @param string $answer Yanıt metni
     * @return array
     * @throws TrendyolApiException
     */
    public function answer(int $question_id, string $answer): array
    {
        $endpoint = sprintf($this->endpoint_path . '/%s/answers', $this->supplier_id, $question_id);
        
        return $this->request('POST', $endpoint, [
            'json' => [
                'text' => $answer,
            ],
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }
    
    /**
     * Müşteri sorusunu iletir/eskalasyon yapar.
     *
     * @param int $question_id Soru ID
     * @param string $reason Eskalasyon nedeni
     * @return array
     * @throws TrendyolApiException
     */
    public function escalate(int $question_id, string $reason): array
    {
        $endpoint = sprintf($this->endpoint_path . '/%s/escalate', $this->supplier_id, $question_id);
        
        return $this->request('POST', $endpoint, [
            'json' => [
                'reason' => $reason,
            ],
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }
} 