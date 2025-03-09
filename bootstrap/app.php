<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e) {
            if ($e instanceof Illuminate\Auth\AuthenticationException) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access. Please log in.',
                ], 401);
            }
        
            if ($e instanceof Illuminate\Http\Exceptions\ThrottleRequestsException) {
                return response()->json([
                    'status' => false,
                    'message' => 'Too many requests. Please slow down.',
                ], 429);
            }
        
            if ($e instanceof Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                return response()->json([
                    'status' => false,
                    'message' => 'Resource not found.',
                ], 404);
            }
        
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null,
            ], 500);
        });
    })->create();
