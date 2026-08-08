<?php

namespace App\Core\Shared\Exceptions;

use App\Core\Shared\Http\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Enforces the locked error shape (12_API_STANDARD.md §7, PDL-045):
 *   { "data": null, "meta": {...}, "error": { code, message, details,
 *     request_id, timestamp } }
 * for every API exception, not just the ones individual Controllers
 * explicitly catch (see e.g. SubmitProjectController's DomainException
 * catch, which builds the same shape by hand for its one known case).
 *
 * WAJIB: register in bootstrap/app.php (Laravel 11) via
 *   ->withExceptions(fn ($exceptions) => $exceptions->render(...))
 * or as the bound `Illuminate\Contracts\Debug\ExceptionHandler` in
 * config/app.php (Laravel <= 10). Referenced from ApiResponse.php's doc
 * comment; not wired automatically by this file's mere existence.
 */
class ApiExceptionHandler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        if (! $request->is('v1/*') && ! $request->expectsJson()) {
            return parent::render($request, $e);
        }

        return match (true) {
            $e instanceof ValidationException => ApiResponse::error(
                'VALIDATION_FAILED',
                'The given data was invalid.',
                $e->errors(),
                422
            ),
            $e instanceof ModelNotFoundException => ApiResponse::error(
                'RESOURCE_NOT_FOUND',
                'The requested resource could not be found.',
                null,
                404
            ),
            $e instanceof AuthorizationException => ApiResponse::error(
                'FORBIDDEN',
                'You are not authorized to perform this action.',
                null,
                403
            ),
            default => $this->renderUnhandled($e, $request),
        };
    }

    /**
     * Deliberately conservative: never leak an unhandled exception's raw
     * message/trace into the API response body (no stack traces to
     * clients, regardless of APP_DEBUG — that belongs in logs only,
     * 11_SECURITY_ARCHITECTURE.md's no-sensitive-data-in-responses rule).
     */
    private function renderUnhandled(Throwable $e, Request $request): JsonResponse
    {
        report($e);

        return ApiResponse::error(
            'INTERNAL_ERROR',
            'An unexpected error occurred.',
            null,
            500
        );
    }
}
