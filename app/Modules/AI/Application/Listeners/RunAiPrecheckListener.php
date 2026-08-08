<?php

namespace App\Modules\AI\Application\Listeners;

use App\Modules\AI\Application\Jobs\RunAiComplianceJob;
use App\Modules\AI\Domain\Events\AiCompliancePrecheckRequested;

/**
 * RunAiPrecheckListener — "AiCompliancePrecheckRequested -> dispatch(RunAiComplianceJob)".
 *
 * Also NOT ShouldQueue itself (dispatching a job is a cheap, synchronous
 * call) — RunAiComplianceJob is what actually goes on the queue and does
 * the (currently Null-gatewayed) provider work.
 */
class RunAiPrecheckListener
{
    public function handle(AiCompliancePrecheckRequested $event): void
    {
        RunAiComplianceJob::dispatch($event->projectId);
    }
}
