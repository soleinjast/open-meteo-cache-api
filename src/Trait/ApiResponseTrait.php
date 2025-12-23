<?php

namespace App\Trait;

use App\Enum\ApiMessage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponseTrait
{
    protected function success(
        mixed $data = null,
        int $status = Response::HTTP_OK,
        array $meta = []
    ): JsonResponse {
        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'meta' => $meta,
        ], $status);
    }

    protected function error(
        ApiMessage|string $message,
        int $status = Response::HTTP_BAD_REQUEST,
        array $errors = []
    ): JsonResponse {
        return new JsonResponse([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
