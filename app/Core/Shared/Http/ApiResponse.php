<?php

namespace App\Core\Shared\Http;

use Illuminate\Http\JsonResponse;

/**
 * ApiResponse (12_API_STANDARD.md §2, §12; PDL-044, PDL-045)
 *
 * The single, shared place the {data, meta, error} envelope and the locked
 * {code, message, details, request_id, timestamp} error shape are built.
 * WAJIB: no Controller constructs an error response manually — every error
 * that needs a JSON shape goes through App\Core\Shared\Http\ApiExceptionHandler
 * (which calls error() below), not ad hoc per-endpoint code.
 */
class ApiResponse
{
    public static function success(mixed $data = null, array $meta = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => array_merge([
                'request_id' => request()->attributes->get('request_id'),
                'timestamp' => now()->toIso8601String(),
            ], $meta),
            'error' => null,
        ], $status);
    }

    public static function error(string $code, string $message, mixed $details = null, int $status = 400): JsonResponse
    {
        return response()->json([
            'data' => null,
            'meta' => [
                'request_id' => request()->attributes->get('request_id'),
                'timestamp' => now()->toIso8601String(),
            ],
            'error' => [
                'code' => $code,
                'message' => $message,
                // WAJIB (PDL-045): never a stack trace, file path, or query text.
                'details' => $details,
                'request_id' => request()->attributes->get('request_id'),
                'timestamp' => now()->toIso8601String(),
            ],
        ], $status);
    }
}
