<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage: ->middleware('role:admin') or ->middleware('role:company_main,company_branch')
 * Enforced on the server; never rely on the frontend to hide features.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return ApiResponse::error('Unauthenticated', 401);
        }

        if ($user->status !== UserStatus::Active) {
            return ApiResponse::error('Your account is not active', 403);
        }

        if (! in_array($user->role->value, $roles, true)) {
            return ApiResponse::error('You do not have permission to perform this action', 403);
        }

        return $next($request);
    }
}
