<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler
{
    public function __invoke(Throwable $e): JsonResponse
    {
        Log::error('Unhandled Exception', [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        if ($e instanceof AuthenticationException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated. You must be logged in to access this resource.',
                'code' => 401
            ], 401);
        }

        if ($e instanceof AuthorizationException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. You do not have permission to access this resource.',
                'code' => 403
            ], 403);
        }

        if ($e instanceof ModelNotFoundException) {
            $model = class_basename($e->getModel());
            return response()->json([
                'status' => 'error',
                'message' => "{$model} not found.",
                'code' => 404
            ], 404);
        }

        if ($e instanceof NotFoundHttpException) {
            $previous = $e->getPrevious();
            if ($previous instanceof ModelNotFoundException) {
                $model = class_basename($previous->getModel());

                return response()->json([
                    'status' => 'error',
                    'message' => "{$model} not found.",
                    'code' => 404
                ], 404);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Not found.',
                'code' => 404
            ], 404);
        }

        if ($e instanceof ValidationException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
                'code' => 422
            ], 422);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Server error.',
            'error' => config('app.debug') ? $e->getMessage() : null,
            'code' => 500
        ], 500);
    }
}
