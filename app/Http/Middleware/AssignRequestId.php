<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * AssignRequestId (PDL-044, 12_API_STANDARD.md §14, 14_LARAVEL_BLUEPRINT.md §9)
 *
 * WAJIB: runs FIRST in the middleware stack, unconditionally — even a request
 * that will later fail authentication still carries a request_id on its
 * logged failure. Honors a client-supplied X-Request-ID; generates one if
 * absent. Never skipped, never optional.
 */
class AssignRequestId
{
    public function handle(Request $request, Closure $next)
    {
        $requestId = $request->header('X-Request-ID') ?: (string) Str::uuid();

        $request->attributes->set('request_id', $requestId);

        $response = $next($request);
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
}
