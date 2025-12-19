<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class WeatherController
{
    #[Route('/api/weather', name: 'api_weather', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Hello from Symfony controller',
        ]);
    }
}
