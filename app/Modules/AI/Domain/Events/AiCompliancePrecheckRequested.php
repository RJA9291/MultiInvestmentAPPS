<?php

namespace App\Modules\AI\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * AiCompliancePrecheckRequested — the intermediate event between
 * ProjectSubmitted/ProjectResubmitted and the actual queued AI work
 * (RunAiComplianceJob). Splitting "a pre-check was requested" from "a
 * pre-check is running" gives a real, listenable moment for any future
 * consumer that only cares about intent (e.g. a UI "AI analysis queued..."
 * status) without depending on the AI Module's queue internals.
 *
 * Naming note: uses the "Ai" prefix (not "AI") for casing consistency with
 * every other AI Module class introduced this Sprint (AiRecommendation,
 * AiPrecheckResult, AiResponseValidator, AiComplianceResultRepositoryInterface,
 * AiProviderGatewayInterface) — a StudlyCase acronym convention already
 * established, applied here too rather than introduced as an exception.
 */
class AiCompliancePrecheckRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly string $projectId)
    {
    }
}
