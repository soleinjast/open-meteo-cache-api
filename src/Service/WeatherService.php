<?php

namespace App\Service;

use App\Constant\WeatherConfig;
use App\DTO\OpenMeteo\ForecastOptions;
use App\Service\External\OpenMeteo\OpenMeteoClient;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Weather service.
 * @property $cacheService
 */
readonly class WeatherService
{
    public function __construct(
        private OpenMeteoClient $openMeteoClient,
        private CacheService $cacheService
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getBerlinForecast(): array
    {
        return $this->cacheService->remember(
            key: WeatherConfig::BERLIN_FORECAST_CACHE_KEY,
            callback: fn() => $this->fetchBerlinWeather(),
            ttl: WeatherConfig::CACHE_TTL
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function fetchBerlinWeather(): array
    {
        return $this->openMeteoClient->fetchForecast(
            latitude: 52.52,
            longitude: 13.41,
            options: (new ForecastOptions())
                ->setCurrent(['temperature_2m'])
                ->setHourly(['temperature_2m'])
                ->setForecastHours(1)
        );
    }
}
