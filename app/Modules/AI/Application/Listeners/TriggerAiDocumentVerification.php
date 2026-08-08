<?php

namespace App\Modules\AI\Application\Listeners;

use App\Modules\AI\Domain\Events\AiDocumentVerificationRequested;
use App\Modules\Document\Domain\Events\DocumentUploaded;

/**
 * TriggerAiDocumentVerification — "DocumentUploaded -> AiDocumentVerificationRequested".
 * Not ShouldQueue itself (cheap relay) — mirrors TriggerAiPrecheckListener.
 * Cross-Module reaction (PDL-020): reacts to Document Module's event via a
 * listener living in AI Module's own ServiceProvider.
 */
class TriggerAiDocumentVerification
{
    public function handle(DocumentUploaded $event): void
    {
        AiDocumentVerificationRequested::dispatch($event->documentId, $event->projectId);
    }
}
