<?php

namespace App\Tests\Controller;

use App\Enum\ApiMessage;
use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Exception\CacheException;

class WeatherControllerTest extends WebTestCase
{
    public function testIndexSuccessReturnsExpectedJson(): void
    {
        $client = static::createClient();

        $stubService = $this->createStub(WeatherService::class);
        $expected = ['foo' => 'bar'];
        $stubService->method('getBerlinForecast')->willReturn($expected);

        static::getContainer()->set(WeatherService::class, $stubService);

        $client->request('GET', '/api/weather');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $json = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertTrue($json['success']);
        $this->assertSame($expected, $json['data']);
        $this->assertArrayHasKey('meta', $json);
        $this->assertSame([], $json['meta']);
    }

    public function testIndexFailureReturnsInternalError(): void
    {
        $client = static::createClient();

        $stubService = $this->createStub(WeatherService::class);
        $stubService->method('getBerlinForecast')->willThrowException(new \RuntimeException('boom'));

        static::getContainer()->set(WeatherService::class, $stubService);

        $client->request('GET', '/api/weather');

        $this->assertResponseStatusCodeSame(500);

        $json = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertFalse($json['success']);
        $this->assertSame(ApiMessage::INTERNAL_ERROR->value, $json['message']);
        $this->assertArrayHasKey('errors', $json);
        $this->assertSame([], $json['errors']);
    }

    public function testIndexFailureWhenRedisIsUnavailable(): void
    {
        $client = static::createClient();

        $stubService = $this->createStub(WeatherService::class);
        $stubService
            ->method('getBerlinForecast')
            ->willThrowException(new CacheException('Redis unavailable'));

        static::getContainer()->set(WeatherService::class, $stubService);

        $client->request('GET', '/api/weather');

        $this->assertResponseStatusCodeSame(500);

        $json = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertFalse($json['success']);
        $this->assertSame(ApiMessage::INTERNAL_ERROR->value, $json['message']);
        $this->assertSame([], $json['errors']);
    }
}
