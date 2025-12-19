<?php

namespace App\Tests\Controller;

use App\Controller\WeatherController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class WeatherControllerTest extends WebTestCase
{
    public function testIndexReturnsExpectedJsonResponse(): void
    {
        $controller = new WeatherController();
        $response = $controller->index();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('message', $data);
        $this->assertSame('Hello from Symfony controller', $data['message']);
    }
}
