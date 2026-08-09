<?php

/**
 * Laravel's standard view config — missing from the original hand-written
 * skeleton this Sprint (only app/database/security/cache/queue/logging/
 * services config files were created). Without this file, `config('view.
 * compiled')` resolves to null, which is exactly what causes artisan
 * optimize's ViewClearCommand to throw "View path not found." during
 * deployment, regardless of whether resources/views exists on disk.
 *
 * This app is API-only (no Blade templates served to users), but Laravel's
 * framework-level artisan commands (optimize, view:cache, view:clear) still
 * require this config to exist and be well-formed.
 */
return [
    'paths' => [
        resource_path('views'),
    ],

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views'))
    ),
];
