<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
       dd($request->all());
        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access. Please log in.',
            ], 401);
        }

        if ($exception instanceof ThrottleRequestsException) {
            return response()->json([
                'status' => false,
                'message' => 'Too many requests. Please slow down.',
            ], 429);
        }

        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'status' => false,
                'message' => 'Resource not found.',
            ], 404);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'status' => false,
                'message' => 'HTTP method not allowed.',
            ], 405);
        }

        return response()->json([
            'status' => false,
            'message' => 'Something went wrong. Please try again later.',
            'error' => env('APP_DEBUG') ? $exception->getMessage() : null,
        ], 500);
    }
}
