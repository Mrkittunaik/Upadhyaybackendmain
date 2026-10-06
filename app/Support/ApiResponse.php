<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Consistent JSON envelope: { success, message, data | errors, meta? }
 */
class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'Request successful', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public static function created(mixed $data = null, string $message = 'Created successfully'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    public static function paginated(LengthAwarePaginator $paginator, string $message = 'Request successful'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public static function error(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        $body = ['success' => false, 'message' => $message];
        if ($errors !== null) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $status);
    }
}
