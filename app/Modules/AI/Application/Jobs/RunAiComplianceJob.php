<?php

namespace App\Modules\AI\Application\Jobs;

use App\Modules\AI\Application\Services\ComplianceAssistantService;
use App\Modules\AI\Domain\Events\AiCompliancePrecheckCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * RunAiComplianceJob — the actual async execution boundary (PDL-024) for
 * the AI Compliance Pre-check. Everything before this (TriggerAiPrecheckListener,
 * RunAiPrecheckListener) is cheap event-relaying; everything the Project
 * Owner's brief called "AI failure != system failure" is guaranteed by
 * ComplianceAssistantService::preCheck() itself never throwing (it already
 * catches provider/validation failures and returns an "unavailable" result
 * — see that class's doc comment) — this Job does not need its own
 * try/catch fallback logic duplicating that guarantee.
 *
 * WAJIB: document_types_present is not yet sourced from a real Document
 * Module (not built this pass) — passed as an empty array rather than
 * invented, same flag as the listener this Job replaces.
 */
class RunAiComplianceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly string $projectId)
    {
    }

    public function handle(ComplianceAssistantService $service): void
    {
        $result = $service->preCheck($this->projectId, []);

        AiCompliancePrecheckCompleted::dispatch($this->projectId, $result->toApiPayload());
    }
}
