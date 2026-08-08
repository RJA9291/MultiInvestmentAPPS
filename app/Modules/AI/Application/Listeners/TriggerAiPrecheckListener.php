<?php

namespace App\Modules\AI\Application\Listeners;

use App\Modules\AI\Domain\Events\AiCompliancePrecheckRequested;
use App\Modules\Project\Domain\Events\ProjectResubmitted;
use App\Modules\Project\Domain\Events\ProjectSubmitted;

/**
 * TriggerAiPrecheckListener — "ProjectSubmitted -> AiCompliancePrecheckRequested".
 *
 * Deliberately NOT ShouldQueue: this listener does no I/O and no AI-provider
 * work — it only re-raises intent as a Domain Event within the AI Module's
 * own vocabulary. The real async boundary (PDL-024) is at RunAiComplianceJob,
 * dispatched by RunAiPrecheckListener below. Keeping this step synchronous
 * means a request that submits a Project observes AiCompliancePrecheckRequested
 * as having been raised before the response returns, which is useful for
 * any synchronous test/observability hook without adding real latency.
 *
 * Cross-Module reaction via the Event Bus only (PDL-020) — the Project
 * Module has no direct dependency on the AI Module and does not know this
 * listener exists.
 */
class TriggerAiPrecheckListener
{
    public function handle(ProjectSubmitted|ProjectResubmitted $event): void
    {
        AiCompliancePrecheckRequested::dispatch($event->projectId);
    }
}
