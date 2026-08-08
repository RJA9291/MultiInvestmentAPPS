<?php

namespace App\Modules\Compliance\Application\Listeners;

use App\Modules\Compliance\Application\Services\ComplianceReviewService;
use App\Modules\Project\Domain\Events\ProjectResubmitted;
use App\Modules\Project\Domain\Events\ProjectSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listens for Project Module's ProjectSubmitted/ProjectResubmitted and opens
 * a new Compliance review cycle — the Laravel-layer realization of
 * 06_DOMAIN_MODEL.md §11's Context Map: "Investment -> Compliance,
 * Customer/Supplier via Domain Event" (14_LARAVEL_BLUEPRINT.md §7).
 *
 * WAJIB: queued, per PDL-024 — every async cross-context reaction goes
 * through the Event Bus, never a direct synchronous call from Project's
 * Service into Compliance's Service.
 */
class OpenComplianceReviewCycle implements ShouldQueue
{
    public function __construct(private readonly ComplianceReviewService $complianceReviewService)
    {
    }

    public function handle(ProjectSubmitted|ProjectResubmitted $event): void
    {
        $this->complianceReviewService->openCycle($event->projectId);
    }
}
