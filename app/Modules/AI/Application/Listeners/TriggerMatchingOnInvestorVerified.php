<?php

namespace App\Modules\AI\Application\Listeners;

use App\Modules\AI\Application\Services\AiInvestorMatchingService;
use App\Modules\Investor\Domain\Events\InvestorVerified;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Brief §6 Trigger Events, row 1: "InvestorVerified" -> run matching.
 * Queued (PDL-024) — scoring against every Published Project must never
 * block the synchronous verify-decision request.
 */
class TriggerMatchingOnInvestorVerified implements ShouldQueue
{
    public function __construct(private readonly AiInvestorMatchingService $matching)
    {
    }

    public function handle(InvestorVerified $event): void
    {
        $this->matching->matchInvestorAgainstPublishedProjects($event->investorProfileId);
    }
}
