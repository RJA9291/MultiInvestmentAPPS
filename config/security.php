<?php

/**
 * 11_SECURITY_ARCHITECTURE.md SEC-010/SEC-011, 14_LARAVEL_BLUEPRINT.md §498
 * ("session TTL ... 14_LARAVEL_BLUEPRINT.md configuration value").
 *
 * `jwt_secret` must NEVER be committed or hardcoded (SEC-011) — set
 * JWT_SECRET in .env on the actual server. `session_ttl_minutes` default
 * of 60 is a documented Sprint 12 default (see JwtTokenCodec's own
 * docblock), not a value backed by usage data yet.
 */
return [
    'jwt_secret' => env('JWT_SECRET'),
    'session_ttl_minutes' => (int) env('SESSION_TTL_MINUTES', 60),
];
