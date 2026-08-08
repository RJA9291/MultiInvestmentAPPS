<?php

namespace App\Modules\Project\Application\Listeners;

use App\Modules\Compliance\Domain\Events\ProjectComplianceApproved;
use App\Modules\Project\Application\Services\ProjectPublishingService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Reacts to Compliance Context's ProjectComplianceApproved — the documented
 * Anti-Corruption Layer translation into Investment Context's own
 * ProjectApproved (06_DOMAIN_MODEL.md §3.1).
 */
class OnProjectComplianceApproved implements ShouldQueue
{
    public function __construct(private readonly ProjectPublishingService $projectPublishingService)
    {
    }

    public function handle(ProjectComplianceApproved $event): void
    {
        $this->projectPublishingService->markApprovedFromCompliance($event->projectId);
    }
}
