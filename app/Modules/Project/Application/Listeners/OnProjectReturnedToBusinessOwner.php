<?php

namespace App\Modules\Project\Application\Listeners;

use App\Modules\Compliance\Domain\Events\ProjectReturnedToBusinessOwner;
use App\Modules\Project\Application\Services\ProjectPublishingService;
use Illuminate\Contracts\Queue\ShouldQueue;

/** Reacts to Compliance Context's rejection hand-off (06_DOMAIN_MODEL.md §3.1). */
class OnProjectReturnedToBusinessOwner implements ShouldQueue
{
    public function __construct(private readonly ProjectPublishingService $projectPublishingService)
    {
    }

    public function handle(ProjectReturnedToBusinessOwner $event): void
    {
        $this->projectPublishingService->markReturnedFromCompliance($event->projectId);
    }
}
