<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function (\Illuminate\Http\JsonResponse $response) {
            if ($response->getStatusCode() === 419) {
                return response()->json([
                    'message' => 'Запрос не авторизован.',
                ], HttpFoundationResponse::HTTP_UNAUTHORIZED);
            }

            return $response;
        });

        $exceptions->render(function (\Throwable $exception) {
            if ($exception instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'message' => 'Заполните форму правильно.',
                    'errors' => $exception->errors(),
                ], $exception->status);
            }

            return null; // Let Laravel handle other exceptions
        });
    })->create();
