<?php

namespace App\Modules\AI\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * DocumentAiReviewReady — "notify compliance" signal from the Project
 * Owner's brief §7. Fired whenever an AI Document Verification result is
 * AVAILABLE (not only on high risk — see HandleAiDocumentVerificationCompleted's
 * separate high-risk log warning for that finer-grained case).
 *
 * FLAGGED: no Notification Module exists yet to actually alert a human
 * (same honest gap already flagged for HandleAiPrecheckCompleted). This
 * event exists so that when a Notification Module IS built, it has a
 * ready-made signal to subscribe to — nothing about that future consumer
 * is fabricated here. Not catalogued as an EVT-XXX for the same reason as
 * AiDocumentVerificationRequested/Completed.
 */
class DocumentAiReviewReady
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $documentId,
        public readonly string $projectId,
    ) {
    }
}
