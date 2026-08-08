<?php

/**
 * Laravel 11-style provider registry (14_LARAVEL_BLUEPRINT.md §6 — every
 * Module's own ServiceProvider, "WAJIB: register this provider" per each
 * one's own docblock). Identity is listed first since every other Module's
 * routes now depend on its `jwt.auth`/`role` middleware aliases being
 * registered before routes/api.php is loaded.
 */
return [
    App\Providers\AppServiceProvider::class,
    App\Modules\Identity\IdentityServiceProvider::class,
    App\Modules\Project\ProjectServiceProvider::class,
    App\Modules\Compliance\ComplianceServiceProvider::class,
    App\Modules\DataRoom\DataRoomServiceProvider::class,
    App\Modules\Document\DocumentServiceProvider::class,
    App\Modules\Investor\InvestorServiceProvider::class,
    App\Modules\Notification\NotificationServiceProvider::class,
    App\Modules\Analytics\AnalyticsServiceProvider::class,
    App\Modules\AI\AIServiceProvider::class,
];
