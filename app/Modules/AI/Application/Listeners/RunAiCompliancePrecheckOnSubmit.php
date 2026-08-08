<?php

namespace App\Modules\AI\Application\Listeners;

use App\Modules\AI\Application\Services\ComplianceAssistantService;
use App\Modules\Project\Domain\Events\ProjectResubmitted;
use App\Modules\Project\Domain\Events\ProjectSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * SUPERSEDED — no longer registered in AIServiceProvider.
 *
 * Replaced by a more granular event chain requested by the Project Owner
 * ("AI Pre-check -> Event Flow"): ProjectSubmitted -> TriggerAiPrecheckListener
 * -> AiCompliancePrecheckRequested -> RunAiPrecheckListener -> dispatch(RunAiComplianceJob)
 * -> AiCompliancePrecheckCompleted -> HandleAiPrecheckCompleted.
 *
 * This class did the same underlying work (call ComplianceAssistantService::
 * preCheck() in reaction to submission) but as a single fat Queued Listener,
 * with no intermediate events another consumer could listen to. Kept in the
 * codebase, unregistered, only so a reviewer diffing this Sprint finds an
 * explanation here rather than a silently vanished class.
 */
class RunAiCompliancePrecheckOnSubmit implements ShouldQueue
{
    public function __construct(private readonly ComplianceAssistantService $assistant)
    {
    }

    public function handle(ProjectSubmitted|ProjectResubmitted $event): void
    {
        $this->assistant->preCheck($event->projectId, []);
    }
}
