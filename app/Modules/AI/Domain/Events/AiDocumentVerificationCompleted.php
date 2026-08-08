<?php

namespace App\Modules\AI\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** AiDocumentVerificationCompleted — mirrors AiCompliancePrecheckCompleted. */
class AiDocumentVerificationCompleted
{
    use Dispatchable, SerializesModels;

    /** @param  array<string, mixed>  $result  AiDocumentVerificationResult::toApiPayload() */
    public function __construct(
        public readonly string $documentId,
        public readonly string $projectId,
        public readonly array $result,
    ) {
    }
}
