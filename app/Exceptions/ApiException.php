<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use RuntimeException;

/** Domain error rendered with the standard API envelope (e.g. 409 Conflict). */
class ApiException extends RuntimeException
{
    public function __construct(string $message, public int $status = 400, public mixed $errors = null)
    {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return ApiResponse::error($this->getMessage(), $this->status, $this->errors);
    }
}
