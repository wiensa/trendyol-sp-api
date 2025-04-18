<?php

namespace Serkan\TrendyolSpApi\Support;

/**
 * RateLimiter istekleri hız sınırını kontrol eder
 */
class RateLimiter
{
    /**
     * Rate limiting etkin mi?
     */
    protected bool $enabled;
    
    /**
     * Saniyede izin verilen maksimum istek sayısı
     */
    protected int $max_requests_per_second;
    
    /**
     * Son istekten bu yana geçen mikrosaniye
     */
    protected int $last_request_time = 0;
    
    /**
     * RateLimiter constructor.
     *
     * @param bool $enabled Rate limiting etkin mi
     * @param int $max_requests_per_second Saniyede izin verilen maksimum istek sayısı
     */
    public function __construct(bool $enabled = true, int $max_requests_per_second = 5)
    {
        $this->enabled = $enabled;
        $this->max_requests_per_second = $max_requests_per_second;
    }
    
    /**
     * İstek hızını kontrol eder ve gerekirse bekler.
     *
     * @return void
     */
    public function throttle(): void
    {
        if (!$this->enabled) {
            return;
        }
        
        $current_time = $this->getTimeMicroseconds();
        
        // İlk istek olup olmadığını kontrol et
        if ($this->last_request_time === 0) {
            $this->last_request_time = $current_time;
            return;
        }
        
        // İki istek arasında en az olması gereken mikrosaniye
        $min_time_between_requests = (1000000 / $this->max_requests_per_second);
        
        // Son istekten bu yana geçen süre
        $time_since_last_request = $current_time - $this->last_request_time;
        
        // Eğer istekler çok hızlı gönderiliyorsa, beklememiz gerekiyor
        if ($time_since_last_request < $min_time_between_requests) {
            $sleep_microseconds = (int) ($min_time_between_requests - $time_since_last_request);
            usleep($sleep_microseconds);
        }
        
        $this->last_request_time = $this->getTimeMicroseconds();
    }
    
    /**
     * Geçerli zamanı mikrosaniye olarak döndürür.
     *
     * @return int
     */
    protected function getTimeMicroseconds(): int
    {
        return (int) (microtime(true) * 1000000);
    }
} 