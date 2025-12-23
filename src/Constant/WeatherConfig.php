<?php

namespace App\Constant;

/**
 * Configuration constants for weather service.
 */
final class WeatherConfig
{
    public const string BERLIN_FORECAST_CACHE_KEY = 'weather.berlin.forecast';
    public const int CACHE_TTL = 300; // 5 minutes
    private function __construct()
    {
    }
}
