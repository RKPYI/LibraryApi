<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function success($data = null, string $message = '', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'code' => $code
        ], $code);
    }

    protected function error(string $message = '', array $errors = [], int $code = 400): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
            'code' => $code
        ], $code);
    }
}
