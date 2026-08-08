<?php

namespace App\Modules\AI\Application\Listeners;

use App\Modules\AI\Domain\Events\AiDocumentVerificationCompleted;
use App\Modules\AI\Domain\Events\DocumentAiReviewReady;
use Illuminate\Support\Facades\Log;

/**
 * HandleAiDocumentVerificationCompleted — reactive side effects ONLY.
 *
 * Deliberately does NOT persist to `ai_document_verifications` (unlike the
 * Project Owner's own sketch, which raw-inserted here) — persistence
 * already happened, if the run was available, inside
 * AiDocumentVerificationService via AiDocumentVerificationResultRepositoryInterface,
 * mirroring HandleAiPrecheckCompleted's identical reasoning for the
 * Compliance pipeline (single write path, no double-write risk).
 */
class HandleAiDocumentVerificationCompleted
{
    public function handle(AiDocumentVerificationCompleted $event): void
    {
        if (! ($event->result['available'] ?? false)) {
            return;
        }

        // "Store Result -> Notify Compliance" (Project Owner's flow diagram,
        // §1) — fired whenever a result is available, regardless of risk.
        DocumentAiReviewReady::dispatch($event->documentId, $event->projectId);

        $riskFlags = $event->result['risk_flags'] ?? [];

        if (is_array($riskFlags) && count($riskFlags) > 0) {
            Log::warning('AI document verification flagged risk(s) on a document.', [
                'document_id' => $event->documentId,
                'project_id' => $event->projectId,
                'risk_flags' => $riskFlags,
                'recommendation' => $event->result['recommendation'] ?? null,
                // FLAGGED: no Notification Module exists yet — same honest
                // gap already noted for HandleAiPrecheckCompleted.
            ]);
        }
    }
}
