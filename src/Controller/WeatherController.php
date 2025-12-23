<?php

namespace App\Controller;

use App\Enum\ApiMessage;
use App\Service\WeatherService;
use App\Trait\ApiResponseTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class WeatherController extends AbstractController
{
    use ApiResponseTrait;
    #[Route('/api/weather', name: 'api_weather', methods: ['GET'])]
    public function index(WeatherService $weatherService): JsonResponse
    {
        try {
            return $this->success($weatherService->getBerlinForecast());
        } catch (\Throwable $e) {
            return $this->error(
                message: ApiMessage::INTERNAL_ERROR,
                status: 500
            );
        }
    }
}
