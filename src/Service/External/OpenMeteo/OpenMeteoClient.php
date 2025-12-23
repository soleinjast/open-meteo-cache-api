<?php

namespace App\Service\External\OpenMeteo;

use App\DTO\OpenMeteo\ForecastOptions;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Client for interacting with the Open-Meteo Weather Forecast API.
 */
readonly class OpenMeteoClient
{
    /**
     * Array parameter names that should be comma-separated.
     */
    private const array ARRAY_PARAMS = ['hourly', 'daily', 'current', 'models'];

    /**
     * Scalar parameter names that are passed as-is.
     */
    private const array SCALAR_PARAMS = [
        'temperature_unit',
        'wind_speed_unit',
        'precipitation_unit',
        'timeformat',
        'timezone',
        'past_days',
        'forecast_days',
        'forecast_hours',
        'forecast_minutely_15',
        'past_hours',
        'past_minutely_15',
        'start_date',
        'end_date',
        'start_hour',
        'end_hour',
        'start_minutely_15',
        'end_minutely_15',
        'cell_selection',
    ];

    public function __construct(
        private HttpClientInterface $httpClient,
        private string              $apiBaseUrl
    ) {
    }

    /**
     * Fetch weather forecast from Open-Meteo API.
     *
     * @param float|array $latitude Latitude coordinate(s)
     * @param float|array $longitude Longitude coordinate(s)
     * @param ForecastOptions|null $options Forecast configuration options
     *
     * @return array The forecast data from the API
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function fetchForecast(
        float|array $latitude,
        float|array $longitude,
        ?ForecastOptions $options = null
    ): array {
        $optionsArray = $options?->toArray() ?? [];
        $queryParams = $this->buildQueryParams($latitude, $longitude, $optionsArray);

        $response = $this->httpClient->request('GET', $this->apiBaseUrl . '/forecast', [
            'query' => $queryParams,
        ]);

        return $response->toArray();
    }

    /**
     * Build query parameters for the API request.
     */
    private function buildQueryParams(
        float|array $latitude,
        float|array $longitude,
        array $options
    ): array {
        $params = [
            'latitude' => is_array($latitude) ? implode(',', $latitude) : $latitude,
            'longitude' => is_array($longitude) ? implode(',', $longitude) : $longitude,
        ];

        // Handle elevation separately as it can be arrayed or scalar
        if (isset($options['elevation'])) {
            $params['elevation'] = is_array($options['elevation'])
                ? implode(',', $options['elevation'])
                : $options['elevation'];
        }

        // Add array parameters (comma-separated)
        foreach (self::ARRAY_PARAMS as $arrayParam) {
            if (!empty($options[$arrayParam]) && is_array($options[$arrayParam])) {
                $params[$arrayParam] = implode(',', $options[$arrayParam]);
            }
        }

        // Add scalar parameters
        foreach (self::SCALAR_PARAMS as $param) {
            if (isset($options[$param])) {
                $params[$param] = $options[$param];
            }
        }

        return $params;
    }
}
