<?php

if (! function_exists('make')) {
    /**
     * @template TClass
     *
     * @param  class-string<TClass>  $abstract
     * @return TClass
     */
    function make(string $abstract, array $parameters = [])
    {
        return \Illuminate\Container\Container::getInstance()->make($abstract, $parameters);
    }
}

if (! function_exists('trendyol')) {
    /**
     * Trendyol API client'ına erişim sağlar.
     *
     * @return \TrendyolApi\TrendyolSpApi\Trendyol
     */
    function trendyol()
    {
        return app('trendyol');
    }
}
