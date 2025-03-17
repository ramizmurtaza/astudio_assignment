<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        api: __DIR__ . '/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register global middleware here
    })
    ->withExceptions(function (Exceptions $exceptions) {

        /**
         * Handle "Not Found" exceptions (404 errors).
         *
         * @param NotFoundHttpException $exception
         * @param Request $request
         * @return JsonResponse
         */
        $exceptions->render(function (NotFoundHttpException $exception, Request $request): JsonResponse {
            return response()->json([
                'status'  => false,
                'message' => 'Route not found',
                'error'   => $exception->getMessage(),
            ], 404);
        });

        /**
         * Handle generic exceptions and return a JSON response.
         *
         * @param Exception $exception
         * @param Request $request
         * @return JsonResponse
         */
        $exceptions->render(function (Exception $exception, Request $request): JsonResponse {

            // Log detailed error information
            Log::error('Exception Occurred: ' . $exception->getMessage(), [
                'file'  => $exception->getFile(),
                'line'  => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $exception->getMessage(),
            ], $exception instanceof HttpException ? $exception->getStatusCode() : 500);
        });

    })->create();
