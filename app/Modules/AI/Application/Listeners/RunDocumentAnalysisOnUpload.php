<?php

namespace App\Modules\AI\Application\Listeners;

use App\Modules\AI\Application\Jobs\RunDocumentAnalysisJob;
use App\Modules\Document\Domain\Events\DocumentUploaded;

/**
 * RunDocumentAnalysisOnUpload — "DocumentUploaded -> dispatch(RunDocumentAnalysisJob)".
 *
 * SUPERSEDED (not deleted — same pattern as RunAiCompliancePrecheckOnSubmit):
 * this was the single-listener, log-only, scope-proportional hook built in
 * v3.22.0 for a period when only the simpler brief existed. The Project
 * Owner has since specified the full "AI Document Verification Module"
 * brief — a granular Requested/Job/Completed event chain with its own
 * persisted results table (`ai_document_verifications`, DB-045) — now built
 * as TriggerAiDocumentVerification / RunAiDocumentVerificationListener /
 * RunAiDocumentVerificationJob / HandleAiDocumentVerificationCompleted.
 * This listener is UNREGISTERED in AIServiceProvider; left here only so the
 * supersession is visible in history rather than silently deleted.
 */
class RunDocumentAnalysisOnUpload
{
    public function handle(DocumentUploaded $event): void
    {
        RunDocumentAnalysisJob::dispatch(
            $event->documentId,
            $event->projectId,
            $event->documentType,
            $event->fileMetadata,
        );
    }
}
