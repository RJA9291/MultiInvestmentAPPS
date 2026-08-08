<?php

namespace App\Modules\AI\Application\Jobs;

use App\Modules\AI\Application\Services\AiDocumentVerificationService;
use App\Modules\AI\Domain\Events\AiDocumentVerificationCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * RunAiDocumentVerificationJob — the async execution boundary (PDL-024),
 * mirrors RunAiComplianceJob exactly. AiDocumentVerificationService::verify()
 * never throws out of this Job (catches internally, degrades to
 * "unavailable"), so no extra try/catch fallback logic is needed here.
 */
class RunAiDocumentVerificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $documentId,
        private readonly string $projectId,
    ) {
    }

    public function handle(AiDocumentVerificationService $service): void
    {
        $result = $service->verify($this->documentId, $this->projectId);

        AiDocumentVerificationCompleted::dispatch($this->documentId, $this->projectId, $result->toApiPayload());
    }
}
