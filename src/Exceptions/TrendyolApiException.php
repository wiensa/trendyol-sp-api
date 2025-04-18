<?php

namespace TrendyolApi\TrendyolSpApi\Exceptions;

use Exception;

class TrendyolApiException extends Exception
{
    /**
     * TrendyolApiException yapıcı.
     *
     * @param string $message Hata mesajı
     * @param int $code Hata kodu
     * @param Exception|null $previous Önceki istisna
     */
    public function __construct(string $message = "", int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
} 