<?php

namespace App\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    protected function ok(mixed $data = null, string $message = 'Request successful'): JsonResponse
    {
        return ApiResponse::success($data, $message);
    }

    protected function created(mixed $data = null, string $message = 'Created successfully'): JsonResponse
    {
        return ApiResponse::created($data, $message);
    }

    protected function fail(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        return ApiResponse::error($message, $status, $errors);
    }
}
