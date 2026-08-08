<?php

namespace App\Modules\AI\Application\Listeners;

use App\Modules\AI\Application\Services\AiInvestorMatchingService;
use App\Modules\Project\Domain\Events\ProjectPublished;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Brief §6 Trigger Events, row 2: "ProjectPublished" -> run matching.
 * Queued (PDL-024), mirroring `TriggerMatchingOnInvestorVerified`.
 *
 * The brief's third trigger, "InvestorProfileUpdated", is deliberately NOT
 * wired — no such event exists in `07_EVENT_CATALOG.md`, and `InvestorProfile`
 * (Investor Module) has no profile-edit capability built yet (only
 * register/verify/reject). Flagged as a tracked gap, not fabricated with an
 * event that does not exist.
 */
class TriggerMatchingOnProjectPublished implements ShouldQueue
{
    public function __construct(private readonly AiInvestorMatchingService $matching)
    {
    }

    public function handle(ProjectPublished $event): void
    {
        $this->matching->matchProjectAgainstVerifiedInvestors($event->projectId);
    }
}
