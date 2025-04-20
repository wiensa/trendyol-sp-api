<?php

namespace TrendyolApi\TrendyolSpApi\Traits;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use TrendyolApi\TrendyolSpApi\Exceptions\TrendyolApiException;

trait ApiRequest
{
    /**
     * HTTP GET isteği gönderir
     *
     * @param string $endpoint API endpoint
     * @param array $query URL sorgu parametreleri
     * @param array $headers İsteğe özel HTTP başlıkları
     * @return array|null İstek yanıtı 
     */
    public function get(string $endpoint, array $query = [], array $headers = []): ?array
    {
        return $this->request('GET', $endpoint, [], $query, $headers);
    }

    /**
     * HTTP POST isteği gönderir
     *
     * @param string $endpoint API endpoint
     * @param array $data İstek gövdesi verileri
     * @param array $query URL sorgu parametreleri
     * @param array $headers İsteğe özel HTTP başlıkları
     * @return array|null İstek yanıtı
     */
    public function post(string $endpoint, array $data = [], array $query = [], array $headers = []): ?array
    {
        return $this->request('POST', $endpoint, $data, $query, $headers);
    }

    /**
     * HTTP PUT isteği gönderir
     *
     * @param string $endpoint API endpoint
     * @param array $data İstek gövdesi verileri
     * @param array $query URL sorgu parametreleri
     * @param array $headers İsteğe özel HTTP başlıkları
     * @return array|null İstek yanıtı
     */
    public function put(string $endpoint, array $data = [], array $query = [], array $headers = []): ?array
    {
        return $this->request('PUT', $endpoint, $data, $query, $headers);
    }

    /**
     * HTTP DELETE isteği gönderir
     *
     * @param string $endpoint API endpoint
     * @param array $data İstek gövdesi verileri
     * @param array $query URL sorgu parametreleri
     * @param array $headers İsteğe özel HTTP başlıkları
     * @return array|null İstek yanıtı
     */
    public function delete(string $endpoint, array $data = [], array $query = [], array $headers = []): ?array
    {
        return $this->request('DELETE', $endpoint, $data, $query, $headers);
    }

    /**
     * HTTP isteği gönderir
     *
     * @param string $method HTTP metodu
     * @param string $endpoint API endpoint
     * @param array $data İstek gövdesi verileri
     * @param array $query URL sorgu parametreleri
     * @param array $headers İsteğe özel HTTP başlıkları
     * @param int|null $retry_attempt Mevcut yeniden deneme sayısı
     * @return array|null İstek yanıtı
     * @throws TrendyolApiException
     */
    public function request(
        string $method, 
        string $endpoint, 
        array $data = [], 
        array $query = [], 
        array $headers = [],
        ?int $retry_attempt = 0
    ): ?array {
        $options = [
            'headers' => $headers,
            'query' => $query,
            'timeout' => config('trendyol.request.timeout', 30),
            'connect_timeout' => config('trendyol.request.connect_timeout', 10),
        ];

        if (!empty($data)) {
            $options['json'] = $data;
        }

        try {
            $response = $this->http_client->request($method, $endpoint, $options);
            $status_code = $response->getStatusCode();
            $contents = $response->getBody()->getContents();
            $decoded = json_decode($contents, true);
            
            if ($status_code >= 400) {
                throw new TrendyolApiException(
                    $decoded['errors'][0]['message'] ?? 'API error',
                    $status_code
                );
            }
            
            return $decoded;
        } catch (ConnectException|RequestException $e) {
            return $this->handleRetriableException($e, $method, $endpoint, $data, $query, $headers, $retry_attempt, $options);
        } catch (GuzzleException $e) {
            $this->handleRequestException($e, $method, $endpoint, $options);
            throw new TrendyolApiException(
                'API request error: ' . $e->getMessage(),
                $e->getCode() ?: 500
            );
        }
    }

    /**
     * Yeniden denenebilir istisnaları yönetir
     *
     * @param GuzzleException $exception Yakalanan hata
     * @param string $method HTTP metodu
     * @param string $endpoint İstek yapılan endpoint
     * @param array $data İstek gövdesi verileri
     * @param array $query URL sorgu parametreleri
     * @param array $headers İsteğe özel HTTP başlıkları
     * @param int $retry_attempt Mevcut yeniden deneme sayısı
     * @param array $options İstek seçenekleri
     * @return array|null İstek yanıtı
     * @throws TrendyolApiException
     */
    protected function handleRetriableException(
        GuzzleException $exception, 
        string $method, 
        string $endpoint, 
        array $data, 
        array $query, 
        array $headers, 
        int $retry_attempt, 
        array $options
    ): ?array {
        $max_attempts = config('trendyol.request.retry_attempts', 3);
        $retry_delay = config('trendyol.request.retry_delay', 1000);

        $status_code = $exception instanceof RequestException && $exception->hasResponse() 
            ? $exception->getResponse()->getStatusCode() 
            : 0;

        $should_retry = $retry_attempt < $max_attempts && 
                      ($exception instanceof ConnectException || 
                       in_array($status_code, [408, 429, 500, 502, 503, 504]));

        if ($should_retry) {
            Log::warning('Trendyol API isteği yeniden deneniyor', [
                'method' => $method,
                'endpoint' => $endpoint,
                'attempt' => $retry_attempt + 1,
                'max_attempts' => $max_attempts,
                'error' => $exception->getMessage(),
                'code' => $status_code,
            ]);

            // Yeniden denemeler arasında bekle (ms)
            $sleep_ms = $retry_delay * ($retry_attempt + 1);
            usleep($sleep_ms * 1000);

            return $this->request($method, $endpoint, $data, $query, $headers, $retry_attempt + 1);
        }

        $this->handleRequestException($exception, $method, $endpoint, $options);
        throw new TrendyolApiException(
            'API request failed after ' . $max_attempts . ' attempts: ' . $exception->getMessage(),
            $status_code ?: 500
        );
    }

    /**
     * İstek hatalarını yönetir ve kaydeder
     *
     * @param GuzzleException $exception Yakalanan hata
     * @param string $method HTTP metodu
     * @param string $endpoint İstek yapılan endpoint
     * @param array $options İstek seçenekleri
     * @return void
     */
    protected function handleRequestException(GuzzleException $exception, string $method, string $endpoint, array $options): void
    {
        $status_code = $exception instanceof RequestException && $exception->hasResponse() 
            ? $exception->getResponse()->getStatusCode() 
            : 0;

        $response_body = $exception instanceof RequestException && $exception->hasResponse() 
            ? $exception->getResponse()->getBody()->getContents() 
            : null;

        Log::error('Trendyol API isteği başarısız oldu', [
            'method' => $method,
            'endpoint' => $endpoint,
            'options' => $options,
            'error' => $exception->getMessage(),
            'code' => $status_code,
            'response' => $response_body,
        ]);
    }
} 