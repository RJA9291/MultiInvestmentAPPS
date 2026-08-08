<?php

use App\Http\Middleware\AssignRequestId;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/**
 * Laravel 11-style bootstrap (chosen per 14_LARAVEL_BLUEPRINT.md §477's
 * open Sprint 12 decision — no config/app.php `providers` array or
 * app/Http/Kernel.php in this skeleton; both are superseded by
 * bootstrap/providers.php and this file).
 *
 * This is an API-only backend (12_API_STANDARD.md) — no `web` routes file,
 * no session/CSRF middleware group.
 */
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            AssignRequestId::class, // PDL-044 — WAJIB, runs before everything else
        ]);

        // jwt.auth / role aliases are registered by IdentityServiceProvider::boot(),
        // not here, so the Identity Module remains self-contained
        // (14_LARAVEL_BLUEPRINT.md §6's "own Service Provider" convention).
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // ApiExceptionHandler already extends Illuminate\Foundation\Exceptions\Handler
        // and overrides render() directly (see its own docblock) — it is
        // bound as the container's ExceptionHandler in
        // App\Providers\AppServiceProvider::register(), not configured here.
    })
    ->create();
