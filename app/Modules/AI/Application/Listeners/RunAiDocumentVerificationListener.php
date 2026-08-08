<?php

namespace App\Modules\AI\Application\Listeners;

use App\Modules\AI\Application\Jobs\RunAiDocumentVerificationJob;
use App\Modules\AI\Domain\Events\AiDocumentVerificationRequested;

/**
 * RunAiDocumentVerificationListener — "AiDocumentVerificationRequested ->
 * dispatch(RunAiDocumentVerificationJob)". Not ShouldQueue itself —
 * dispatching a job is a cheap, synchronous call; the Job is what queues.
 */
class RunAiDocumentVerificationListener
{
    public function handle(AiDocumentVerificationRequested $event): void
    {
        RunAiDocumentVerificationJob::dispatch($event->documentId, $event->projectId);
    }
}
