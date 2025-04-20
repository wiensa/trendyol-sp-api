<?php

namespace TrendyolApi\TrendyolSpApi\Traits;

trait ResponseFormatter
{
    /**
     * API yanıtını standardize eder
     *
     * @param array|null $response Ham API yanıtı
     * @param string|null $field_name Verinin çıkarılacağı alan adı
     * @param bool $is_collection Dönen verinin koleksiyon (liste) olup olmadığı
     * @return array|null Standardize edilmiş yanıt
     */
    protected function formatResponse(?array $response, ?string $field_name = null, bool $is_collection = false): ?array
    {
        if ($response === null) {
            return null;
        }

        // Alan adı belirtilmişse, o alandaki veriyi çıkar
        if ($field_name && isset($response[$field_name])) {
            $data = $response[$field_name];
        } else {
            $data = $response;
        }

        // Koleksiyon formatında ise standart yapıya dönüştür
        if ($is_collection) {
            return [
                'data' => $data,
                'total_count' => $response['totalElements'] ?? $response['totalCount'] ?? count($data),
                'page' => $response['number'] ?? $response['page'] ?? 0,
                'size' => $response['size'] ?? 0,
                'total_pages' => $response['totalPages'] ?? 0,
            ];
        }

        return $data;
    }

    /**
     * Sayfalanmış veriyi standardize eder
     *
     * @param array|null $response Ham API yanıtı
     * @param string $field_name Verinin çıkarılacağı alan adı
     * @return array|null Standardize edilmiş sayfalama yanıtı
     */
    protected function formatPaginatedResponse(?array $response, string $field_name): ?array
    {
        return $this->formatResponse($response, $field_name, true);
    }

    /**
     * Tekil nesne yanıtını standardize eder
     *
     * @param array|null $response Ham API yanıtı
     * @param string|null $field_name Verinin çıkarılacağı alan adı
     * @return array|null Standardize edilmiş tekil nesne yanıtı
     */
    protected function formatSingleResponse(?array $response, ?string $field_name = null): ?array
    {
        return $this->formatResponse($response, $field_name, false);
    }
} 