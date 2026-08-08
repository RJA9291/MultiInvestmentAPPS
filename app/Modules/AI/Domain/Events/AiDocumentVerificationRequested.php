<?php

namespace App\Modules\AI\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * AiDocumentVerificationRequested — AI Module-internal orchestration event,
 * mirroring AiCompliancePrecheckRequested's role in the Compliance pipeline.
 * NOT catalogued in `07_EVENT_CATALOG.md` as an EVT-XXX — same precedent as
 * AiCompliancePrecheckRequested/Completed: this is internal AI-pipeline
 * orchestration, not a cross-Bounded-Context Domain Event.
 */
class AiDocumentVerificationRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $documentId,
        public readonly string $projectId,
    ) {
    }
}
