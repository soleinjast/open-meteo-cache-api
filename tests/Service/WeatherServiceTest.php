<?php

namespace App\Tests\Service;

use App\Constant\WeatherConfig;
use App\DTO\OpenMeteo\ForecastOptions;
use App\Service\CacheService;
use App\Service\External\OpenMeteo\OpenMeteoClient;
use App\Service\WeatherService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WeatherServiceTest extends TestCase
{
    /** @var OpenMeteoClient&MockObject */
    private OpenMeteoClient $openMeteoClient;

    /** @var CacheService&MockObject */
    private CacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->openMeteoClient = $this->createMock(OpenMeteoClient::class);
        $this->cacheService = $this->createMock(CacheService::class);
    }

    public function testGetBerlinForecastFetchesAndCachesWhenMissing(): void
    {
        $expectedResponse = ['current' => ['temperature_2m' => 5.1]];

        // Expect the OpenMeteo client to be called with fixed coordinates and properly built options
        $this->openMeteoClient
            ->expects($this->once())
            ->method('fetchForecast')
            ->with(
                $this->equalTo(52.52),
                $this->equalTo(13.41),
                $this->callback(function ($options) {
                    $this->assertInstanceOf(ForecastOptions::class, $options);
                    $asArray = $options->toArray();

                    // Must include current/hourly temperature_2m and forecast_hours 1
                    $this->assertArrayHasKey('current', $asArray);
                    $this->assertSame(['temperature_2m'], $asArray['current']);

                    $this->assertArrayHasKey('hourly', $asArray);
                    $this->assertSame(['temperature_2m'], $asArray['hourly']);

                    $this->assertArrayHasKey('forecast_hours', $asArray);
                    $this->assertSame(1, $asArray['forecast_hours']);
                    return true;
                })
            )
            ->willReturn($expectedResponse);

        // Simulate cache miss: remember executes callback and returns its result
        $this->cacheService
            ->expects($this->once())
            ->method('remember')
            ->with(
                $this->equalTo(WeatherConfig::BERLIN_FORECAST_CACHE_KEY),
                $this->callback('is_callable'),
                $this->equalTo(WeatherConfig::CACHE_TTL)
            )
            ->willReturnCallback(function (string $key, callable $callback, int $ttl) {
                return $callback();
            });

        $service = new WeatherService($this->openMeteoClient, $this->cacheService);

        $result = $service->getBerlinForecast();

        $this->assertSame($expectedResponse, $result);
    }

    public function testGetBerlinForecastReturnsCachedValueWithoutCallingClient(): void
    {
        $cached = ['cached' => true];

        // Ensure client is NOT called when cache has the data
        $this->openMeteoClient
            ->expects($this->never())
            ->method('fetchForecast');

        // Simulate cache hit: remember returns a value directly (does not call callback)
        $this->cacheService
            ->expects($this->once())
            ->method('remember')
            ->with(
                $this->equalTo(WeatherConfig::BERLIN_FORECAST_CACHE_KEY),
                $this->callback('is_callable'),
                $this->equalTo(WeatherConfig::CACHE_TTL)
            )
            ->willReturn($cached);

        $service = new WeatherService($this->openMeteoClient, $this->cacheService);

        $result = $service->getBerlinForecast();

        $this->assertSame($cached, $result);
    }
}
