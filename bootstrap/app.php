<?php

use App\Http\Middleware\EnsureRole;
use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $isApi = fn (Request $r) => $r->is('api/*') || $r->expectsJson();

        $exceptions->render(fn (ValidationException $e, Request $r) => $isApi($r)
            ? ApiResponse::error('Validation failed', 422, $e->errors()) : null);

        $exceptions->render(fn (AuthenticationException $e, Request $r) => $isApi($r)
            ? ApiResponse::error('Unauthenticated', 401) : null);

        $exceptions->render(fn (AuthorizationException $e, Request $r) => $isApi($r)
            ? ApiResponse::error($e->getMessage() ?: 'Forbidden', 403) : null);

        $exceptions->render(fn (ModelNotFoundException $e, Request $r) => $isApi($r)
            ? ApiResponse::error('Resource not found', 404) : null);

        $exceptions->render(fn (NotFoundHttpException $e, Request $r) => $isApi($r)
            ? ApiResponse::error('Endpoint not found', 404) : null);

        $exceptions->render(fn (HttpExceptionInterface $e, Request $r) => $isApi($r)
            ? ApiResponse::error($e->getMessage() ?: 'Request failed', $e->getStatusCode()) : null);

        $exceptions->render(fn (Throwable $e, Request $r) => $isApi($r) && ! config('app.debug')
            ? ApiResponse::error('Server error', 500) : null);
    })
    ->create();
