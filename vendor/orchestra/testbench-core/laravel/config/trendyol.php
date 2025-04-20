<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Trendyol API Credentials
    |--------------------------------------------------------------------------
    |
    | Here you can configure your Trendyol API credentials which will be used
    | to authenticate API requests.
    |
    */
    'credentials' => [
        'supplier_id' => env('TRENDYOL_SUPPLIER_ID'),
        'api_key' => env('TRENDYOL_API_KEY'),  
        'api_secret' => env('TRENDYOL_API_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Trendyol API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for Trendyol API endpoints.
    |
    */
    'base_url' => env('TRENDYOL_API_URL', 'https://api.trendyol.com/sapigw'),

    /*
    |--------------------------------------------------------------------------
    | Trendyol API Request Settings
    |--------------------------------------------------------------------------
    |
    | Here you can configure API request settings like timeout and retry options.
    |
    */
    'request' => [
        'timeout' => env('TRENDYOL_REQUEST_TIMEOUT', 30),
        'connect_timeout' => env('TRENDYOL_CONNECT_TIMEOUT', 10),
        'retry_attempts' => env('TRENDYOL_RETRY_ATTEMPTS', 3),
        'retry_sleep' => env('TRENDYOL_RETRY_SLEEP', 1000), // milliseconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Trendyol API Cache Settings
    |--------------------------------------------------------------------------
    |
    | Settings for caching API responses.
    |
    */
    'cache' => [
        'enabled' => env('TRENDYOL_CACHE_ENABLED', true),
        'ttl' => env('TRENDYOL_CACHE_TTL', 3600), // seconds
        'prefix' => env('TRENDYOL_CACHE_PREFIX', 'trendyol_'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Trendyol API Debug Mode
    |--------------------------------------------------------------------------
    |
    | When debug mode is enabled, detailed logs will be created.
    |
    */
    'debug' => env('TRENDYOL_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Trendyol API Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure the rate limiting settings to prevent API rate limit errors.
    |
    */
    'rate_limit' => [
        'enabled' => env('TRENDYOL_RATE_LIMIT_ENABLED', true),
        'max_requests_per_second' => env('TRENDYOL_MAX_REQUESTS', 5),
    ],
];
