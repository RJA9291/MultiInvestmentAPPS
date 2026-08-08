<?php

namespace App\Modules\Compliance;

use App\Modules\Compliance\Application\Listeners\OpenComplianceReviewCycle;
use App\Modules\Compliance\Domain\Repositories\ComplianceReviewRepositoryInterface;
use App\Modules\Compliance\Infrastructure\Repositories\EloquentComplianceReviewRepository;
use App\Modules\Project\Domain\Events\ProjectResubmitted;
use App\Modules\Project\Domain\Events\ProjectSubmitted;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Compliance Module's own Service Provider (14_LARAVEL_BLUEPRINT.md §6).
 * WAJIB: register in bootstrap/providers.php / config/app.php.
 */
class ComplianceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ComplianceReviewRepositoryInterface::class, EloquentComplianceReviewRepository::class);
    }

    public function boot(): void
    {
        Event::listen(ProjectSubmitted::class, OpenComplianceReviewCycle::class);
        Event::listen(ProjectResubmitted::class, OpenComplianceReviewCycle::class);
    }
}
