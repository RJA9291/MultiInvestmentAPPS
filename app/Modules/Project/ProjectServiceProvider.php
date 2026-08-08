<?php

namespace App\Modules\Project;

use App\Modules\Compliance\Domain\Events\ProjectComplianceApproved;
use App\Modules\Compliance\Domain\Events\ProjectReturnedToBusinessOwner;
use App\Modules\Project\Application\Listeners\OnProjectComplianceApproved;
use App\Modules\Project\Application\Listeners\OnProjectReturnedToBusinessOwner;
use App\Modules\Project\Domain\Repositories\ProjectRepositoryInterface;
use App\Modules\Project\Infrastructure\Repositories\EloquentProjectRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Project Module's own Service Provider (14_LARAVEL_BLUEPRINT.md §6) — binds
 * this Module's Repository interface, and registers the Listeners this
 * Module owns for events it reacts to (never bound in AppServiceProvider,
 * which would blur Module boundaries, §2).
 *
 * WAJIB: register this provider in bootstrap/providers.php (Laravel 11) or
 * config/app.php's `providers` array (Laravel <= 10) — not done automatically.
 */
class ProjectServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProjectRepositoryInterface::class, EloquentProjectRepository::class);
    }

    public function boot(): void
    {
        Event::listen(ProjectComplianceApproved::class, OnProjectComplianceApproved::class);
        Event::listen(ProjectReturnedToBusinessOwner::class, OnProjectReturnedToBusinessOwner::class);
    }
}
