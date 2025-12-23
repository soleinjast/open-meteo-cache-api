<?php

namespace App\Tests\Service\External\OpenMeteo;

use App\DTO\OpenMeteo\ForecastOptions;
use App\Service\External\OpenMeteo\OpenMeteoClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class OpenMeteoClientTest extends TestCase
{
    private const string API_BASE_URL = 'https://api.open-meteo.com/v1';

    /**
     * Test basic forecast fetch with minimal parameters.
     */
    public function testFetchForecastWithMinimalParameters(): void
    {
        // Arrange
        $expectedData = [
            'latitude' => 52.52,
            'longitude' => 13.41,
            'current' => ['temperature_2m' => 15.5],
            'hourly' => ['temperature_2m' => [15.5, 16.0, 16.5]],
        ];

        $mockResponse = new MockResponse(json_encode($expectedData));
        $httpClient = new MockHttpClient($mockResponse);
        $client = new OpenMeteoClient($httpClient, self::API_BASE_URL);

        // Act
        $result = $client->fetchForecast(52.52, 13.41);

        // Assert
        $this->assertEquals($expectedData, $result);
        $this->assertEquals('GET', $mockResponse->getRequestMethod());
        $this->assertStringContainsString('/forecast', $mockResponse->getRequestUrl());
        $this->assertStringContainsString('latitude=52.52', $mockResponse->getRequestUrl());
        $this->assertStringContainsString('longitude=13.41', $mockResponse->getRequestUrl());
    }

    /**
     * Test forecast fetch with ForecastOptions.
     */
    public function testFetchForecastWithOptions(): void
    {
        // Arrange
        $expectedData = ['test' => 'data'];
        $mockResponse = new MockResponse(json_encode($expectedData));
        $httpClient = new MockHttpClient($mockResponse);
        $client = new OpenMeteoClient($httpClient, self::API_BASE_URL);

        $options = (new ForecastOptions())
            ->setCurrent(['temperature_2m', 'wind_speed_10m'])
            ->setHourly(['temperature_2m', 'precipitation'])
            ->setForecastDays(7)
            ->setTimezone('Europe/Berlin');

        // Act
        $result = $client->fetchForecast(52.52, 13.41, $options);

        // Assert
        $this->assertEquals($expectedData, $result);
        $requestUrl = $mockResponse->getRequestUrl();
        $this->assertStringContainsString('current=temperature_2m%2Cwind_speed_10m', $requestUrl);
        $this->assertStringContainsString('hourly=temperature_2m%2Cprecipitation', $requestUrl);
        $this->assertStringContainsString('forecast_days=7', $requestUrl);
        $this->assertStringContainsString('timezone=Europe/Berlin', $requestUrl);
    }

    /**
     * Test forecast fetch with multiple locations.
     */
    public function testFetchForecastWithMultipleLocations(): void
    {
        // Arrange
        $expectedData = [
            ['latitude' => 52.52, 'longitude' => 13.41],
            ['latitude' => 48.85, 'longitude' => 2.35],
        ];

        $mockResponse = new MockResponse(json_encode($expectedData));
        $httpClient = new MockHttpClient($mockResponse);
        $client = new OpenMeteoClient($httpClient, self::API_BASE_URL);

        // Act
        $result = $client->fetchForecast([52.52, 48.85], [13.41, 2.35]);

        // Assert
        $this->assertEquals($expectedData, $result);
        $requestUrl = $mockResponse->getRequestUrl();
        $this->assertStringContainsString('latitude=52.52%2C48.85', $requestUrl);
        $this->assertStringContainsString('longitude=13.41%2C2.35', $requestUrl);
    }

    /**
     * Test forecast fetch with all option types.
     */
    public function testFetchForecastWithAllOptionTypes(): void
    {
        // Arrange
        $expectedData = ['comprehensive' => 'test'];
        $mockResponse = new MockResponse(json_encode($expectedData));
        $httpClient = new MockHttpClient($mockResponse);
        $client = new OpenMeteoClient($httpClient, self::API_BASE_URL);

        $options = (new ForecastOptions())
            ->setCurrent(['temperature_2m'])
            ->setHourly(['temperature_2m', 'precipitation'])
            ->setDaily(['temperature_2m_max', 'temperature_2m_min'])
            ->setForecastDays(7)
            ->setTemperatureUnit('fahrenheit')
            ->setWindSpeedUnit('mph')
            ->setPrecipitationUnit('inch')
            ->setTimeformat('unixtime')
            ->setTimezone('America/New_York')
            ->setPastDays(3);

        // Act
        $result = $client->fetchForecast(40.7128, -74.0060, $options);

        // Assert
        $this->assertEquals($expectedData, $result);
        $requestUrl = $mockResponse->getRequestUrl();
        $this->assertStringContainsString('temperature_unit=fahrenheit', $requestUrl);
        $this->assertStringContainsString('wind_speed_unit=mph', $requestUrl);
        $this->assertStringContainsString('precipitation_unit=inch', $requestUrl);
        $this->assertStringContainsString('timeformat=unixtime', $requestUrl);
        $this->assertStringContainsString('past_days=3', $requestUrl);
    }

    /**
     * Test forecast fetch with date range options.
     */
    public function testFetchForecastWithDateRange(): void
    {
        // Arrange
        $expectedData = ['date_range' => 'test'];
        $mockResponse = new MockResponse(json_encode($expectedData));
        $httpClient = new MockHttpClient($mockResponse);
        $client = new OpenMeteoClient($httpClient, self::API_BASE_URL);

        $options = (new ForecastOptions())
            ->setStartDate('2024-01-01')
            ->setEndDate('2024-01-07')
            ->setHourly(['temperature_2m']);

        // Act
        $result = $client->fetchForecast(52.52, 13.41, $options);

        // Assert
        $this->assertEquals($expectedData, $result);
        $requestUrl = $mockResponse->getRequestUrl();
        $this->assertStringContainsString('start_date=2024-01-01', $requestUrl);
        $this->assertStringContainsString('end_date=2024-01-07', $requestUrl);
    }

    /**
     * Test forecast fetch with elevation.
     */
    public function testFetchForecastWithElevation(): void
    {
        // Arrange
        $expectedData = ['elevation' => 'test'];
        $mockResponse = new MockResponse(json_encode($expectedData));
        $httpClient = new MockHttpClient($mockResponse);
        $client = new OpenMeteoClient($httpClient, self::API_BASE_URL);

        $options = (new ForecastOptions())
            ->setElevation(100.5)
            ->setHourly(['temperature_2m']);

        // Act
        $result = $client->fetchForecast(52.52, 13.41, $options);

        // Assert
        $this->assertEquals($expectedData, $result);
        $requestUrl = $mockResponse->getRequestUrl();
        $this->assertStringContainsString('elevation=100.5', $requestUrl);
    }

    /**
     * Test forecast fetch without options (null).
     */
    public function testFetchForecastWithoutOptions(): void
    {
        // Arrange
        $expectedData = ['simple' => 'test'];
        $mockResponse = new MockResponse(json_encode($expectedData));
        $httpClient = new MockHttpClient($mockResponse);
        $client = new OpenMeteoClient($httpClient, self::API_BASE_URL);

        // Act
        $result = $client->fetchForecast(52.52, 13.41, null);

        // Assert
        $this->assertEquals($expectedData, $result);
        $requestUrl = $mockResponse->getRequestUrl();
        $this->assertStringContainsString('latitude=52.52', $requestUrl);
        $this->assertStringContainsString('longitude=13.41', $requestUrl);
    }

    /**
     * Test that API errors are properly propagated.
     */
    public function testFetchForecastThrowsExceptionOnApiError(): void
    {
        // Arrange
        $mockResponse = new MockResponse('', ['http_code' => 500]);
        $httpClient = new MockHttpClient($mockResponse);
        $client = new OpenMeteoClient($httpClient, self::API_BASE_URL);

        // Assert
        $this->expectException(ServerExceptionInterface::class);

        // Act
        $client->fetchForecast(52.52, 13.41);
    }

    /**
     * Test that 429 (rate limit) errors are properly propagated.
     */
    public function testFetchForecastThrowsExceptionOnRateLimit(): void
    {
        // Arrange
        $mockResponse = new MockResponse('', ['http_code' => 429]);
        $httpClient = new MockHttpClient($mockResponse);
        $client = new OpenMeteoClient($httpClient, self::API_BASE_URL);

        // Assert
        $this->expectException(ClientExceptionInterface::class);

        // Act
        $client->fetchForecast(52.52, 13.41);
    }

    /**
     * Test that network errors are properly propagated.
     */
    public function testFetchForecastThrowsExceptionOnNetworkError(): void
    {
        // Arrange
        $mockResponse = new MockResponse('', ['error' => 'Network error']);
        $httpClient = new MockHttpClient($mockResponse);
        $client = new OpenMeteoClient($httpClient, self::API_BASE_URL);

        // Assert
        $this->expectException(TransportExceptionInterface::class);

        // Act
        $client->fetchForecast(52.52, 13.41);
    }

    /**
     * Test that invalid JSON response throws exception.
     */
    public function testFetchForecastThrowsExceptionOnInvalidJson(): void
    {
        // Arrange
        $mockResponse = new MockResponse('invalid json{]');
        $httpClient = new MockHttpClient($mockResponse);
        $client = new OpenMeteoClient($httpClient, self::API_BASE_URL);

        // Assert
        $this->expectException(\Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface::class);

        // Act
        $client->fetchForecast(52.52, 13.41);
    }

    /**
     * Test the correct API base URL is used.
     */
    public function testUsesCorrectApiBaseUrl(): void
    {
        // Arrange
        $customBaseUrl = 'https://custom-api.example.com/v2';
        $mockResponse = new MockResponse('[]');
        $httpClient = new MockHttpClient($mockResponse);
        $client = new OpenMeteoClient($httpClient, $customBaseUrl);

        // Act
        $client->fetchForecast(52.52, 13.41);

        // Assert
        $this->assertStringStartsWith($customBaseUrl, $mockResponse->getRequestUrl());
    }

    /**
     * Test models parameter is correctly formatted.
     */
    public function testFetchForecastWithModels(): void
    {
        // Arrange
        $expectedData = ['models' => 'test'];
        $mockResponse = new MockResponse(json_encode($expectedData));
        $httpClient = new MockHttpClient($mockResponse);
        $client = new OpenMeteoClient($httpClient, self::API_BASE_URL);

        $options = (new ForecastOptions())
            ->setModels(['gfs', 'ecmwf'])
            ->setHourly(['temperature_2m']);

        // Act
        $result = $client->fetchForecast(52.52, 13.41, $options);

        // Assert
        $requestUrl = $mockResponse->getRequestUrl();
        $this->assertStringContainsString('models=gfs%2Cecmwf', $requestUrl);
    }

    /**
     * Test cell selection parameter.
     */
    public function testFetchForecastWithCellSelection(): void
    {
        // Arrange
        $expectedData = ['cell' => 'test'];
        $mockResponse = new MockResponse(json_encode($expectedData));
        $httpClient = new MockHttpClient($mockResponse);
        $client = new OpenMeteoClient($httpClient, self::API_BASE_URL);

        $options = (new ForecastOptions())
            ->setCellSelection('sea')
            ->setHourly(['temperature_2m']);

        // Act
        $result = $client->fetchForecast(52.52, 13.41, $options);

        // Assert
        $requestUrl = $mockResponse->getRequestUrl();
        $this->assertStringContainsString('cell_selection=sea', $requestUrl);
    }

    /**
     * Test forecast hours parameter.
     */
    public function testFetchForecastWithForecastHours(): void
    {
        // Arrange
        $expectedData = ['hours' => 'test'];
        $mockResponse = new MockResponse(json_encode($expectedData));
        $httpClient = new MockHttpClient($mockResponse);
        $client = new OpenMeteoClient($httpClient, self::API_BASE_URL);

        $options = (new ForecastOptions())
            ->setForecastHours(24)
            ->setHourly(['temperature_2m']);

        // Act
        $result = $client->fetchForecast(52.52, 13.41, $options);

        // Assert
        $this->assertEquals($expectedData, $result);
        $requestUrl = $mockResponse->getRequestUrl();
        $this->assertStringContainsString('forecast_hours=24', $requestUrl);
    }

    /**
     * Test forecast minutely 15 parameter.
     */
    public function testFetchForecastWithForecastMinutely15(): void
    {
        // Arrange
        $expectedData = ['minutely' => 'test'];
        $mockResponse = new MockResponse(json_encode($expectedData));
        $httpClient = new MockHttpClient($mockResponse);
        $client = new OpenMeteoClient($httpClient, self::API_BASE_URL);

        $options = (new ForecastOptions())
            ->setForecastMinutely15(96)
            ->setHourly(['temperature_2m']);

        // Act
        $result = $client->fetchForecast(52.52, 13.41, $options);

        // Assert
        $this->assertEquals($expectedData, $result);
        $requestUrl = $mockResponse->getRequestUrl();
        $this->assertStringContainsString('forecast_minutely_15=96', $requestUrl);
    }

    /**
     * Test past hours parameter.
     */
    public function testFetchForecastWithPastHours(): void
    {
        // Arrange
        $expectedData = ['past_hours' => 'test'];
        $mockResponse = new MockResponse(json_encode($expectedData));
        $httpClient = new MockHttpClient($mockResponse);
        $client = new OpenMeteoClient($httpClient, self::API_BASE_URL);

        $options = (new ForecastOptions())
            ->setPastHours(12)
            ->setHourly(['temperature_2m']);

        // Act
        $result = $client->fetchForecast(52.52, 13.41, $options);

        // Assert
        $this->assertEquals($expectedData, $result);
        $requestUrl = $mockResponse->getRequestUrl();
        $this->assertStringContainsString('past_hours=12', $requestUrl);
    }

    /**
     * Test past minutely parameter 15.
     */
    public function testFetchForecastWithPastMinutely15(): void
    {
        // Arrange
        $expectedData = ['past_minutely' => 'test'];
        $mockResponse = new MockResponse(json_encode($expectedData));
        $httpClient = new MockHttpClient($mockResponse);
        $client = new OpenMeteoClient($httpClient, self::API_BASE_URL);

        $options = (new ForecastOptions())
            ->setPastMinutely15(48)
            ->setHourly(['temperature_2m']);

        // Act
        $result = $client->fetchForecast(52.52, 13.41, $options);

        // Assert
        $this->assertEquals($expectedData, $result);
        $requestUrl = $mockResponse->getRequestUrl();
        $this->assertStringContainsString('past_minutely_15=48', $requestUrl);
    }

    /**
     * Test start hour and end hour parameters.
     */
    public function testFetchForecastWithHourRange(): void
    {
        // Arrange
        $expectedData = ['hour_range' => 'test'];
        $mockResponse = new MockResponse(json_encode($expectedData));
        $httpClient = new MockHttpClient($mockResponse);
        $client = new OpenMeteoClient($httpClient, self::API_BASE_URL);

        $options = (new ForecastOptions())
            ->setStartHour('2024-01-01T00:00')
            ->setEndHour('2024-01-01T23:00')
            ->setHourly(['temperature_2m']);

        // Act
        $result = $client->fetchForecast(52.52, 13.41, $options);

        // Assert
        $this->assertEquals($expectedData, $result);
        $requestUrl = $mockResponse->getRequestUrl();
        $this->assertStringContainsString('start_hour=2024-01-01T00:00', $requestUrl);
        $this->assertStringContainsString('end_hour=2024-01-01T23:00', $requestUrl);
    }

    /**
     * Test start and end minutely with 15 parameters.
     */
    public function testFetchForecastWithMinutely15Range(): void
    {
        // Arrange
        $expectedData = ['minutely_range' => 'test'];
        $mockResponse = new MockResponse(json_encode($expectedData));
        $httpClient = new MockHttpClient($mockResponse);
        $client = new OpenMeteoClient($httpClient, self::API_BASE_URL);

        $options = (new ForecastOptions())
            ->setStartMinutely15('2024-01-01T00:00')
            ->setEndMinutely15('2024-01-01T01:00')
            ->setHourly(['temperature_2m']);

        // Act
        $result = $client->fetchForecast(52.52, 13.41, $options);

        // Assert
        $this->assertEquals($expectedData, $result);
        $requestUrl = $mockResponse->getRequestUrl();
        $this->assertStringContainsString('start_minutely_15=2024-01-01T00:00', $requestUrl);
        $this->assertStringContainsString('end_minutely_15=2024-01-01T01:00', $requestUrl);
    }

    /**
     * Test multiple elevations for multiple locations.
     */
    public function testFetchForecastWithMultipleElevations(): void
    {
        // Arrange
        $expectedData = ['multi_elevation' => 'test'];
        $mockResponse = new MockResponse(json_encode($expectedData));
        $httpClient = new MockHttpClient($mockResponse);
        $client = new OpenMeteoClient($httpClient, self::API_BASE_URL);

        $options = (new ForecastOptions())
            ->setElevation([100.5, 250.0])
            ->setHourly(['temperature_2m']);

        // Act
        $result = $client->fetchForecast([52.52, 48.85], [13.41, 2.35], $options);

        // Assert
        $this->assertEquals($expectedData, $result);
        $requestUrl = $mockResponse->getRequestUrl();
        $this->assertStringContainsString('elevation=100.5%2C250', $requestUrl);
    }

    /**
     * Test that an empty options object doesn't add unnecessary parameters.
     */
    public function testFetchForecastWithEmptyOptions(): void
    {
        // Arrange
        $expectedData = ['empty' => 'test'];
        $mockResponse = new MockResponse(json_encode($expectedData));
        $httpClient = new MockHttpClient($mockResponse);
        $client = new OpenMeteoClient($httpClient, self::API_BASE_URL);

        $options = new ForecastOptions(); // Empty options

        // Act
        $result = $client->fetchForecast(52.52, 13.41, $options);

        // Assert
        $this->assertEquals($expectedData, $result);
        $requestUrl = $mockResponse->getRequestUrl();

        // Should only contain latitude and longitude
        $this->assertStringContainsString('latitude=52.52', $requestUrl);
        $this->assertStringContainsString('longitude=13.41', $requestUrl);

        // Should NOT contain optional parameters
        $this->assertStringNotContainsString('hourly=', $requestUrl);
        $this->assertStringNotContainsString('daily=', $requestUrl);
        $this->assertStringNotContainsString('current=', $requestUrl);
    }
}
