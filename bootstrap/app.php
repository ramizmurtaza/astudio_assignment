<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        
        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            return response()->json(['status' => false, 'message' => 'route not found', 'error' => $exception->getMessage()]);
        });

        $exceptions->render(function (ValidationException $exception, Request $request) {
            return response()->json(['status' => false, 'message' => 'invalid information', 'error' => $exception->errors()]);
        });

        $exceptions->render(function (Exception $exception, Request $request) {
            
            return response()->json(['status' => false, 'message' => 'something went wrong', 'error' => $exception->getMessage()]);
        });

    })->create();
