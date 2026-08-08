<?php

namespace App\Modules\AI\Application\Jobs;

use App\Modules\AI\Application\Services\DocumentAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * RunDocumentAnalysisJob — the async execution boundary (PDL-024) for the
 * Document Module's AI-on-upload hook. Mirrors RunAiComplianceJob's shape;
 * DocumentAnalysisService::analyze() itself never throws out of this Job
 * (it catches provider failures internally and only logs), so no extra
 * try/catch is needed here either.
 */
class RunDocumentAnalysisJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @param  array<string, mixed>  $fileMetadata */
    public function __construct(
        private readonly string $documentId,
        private readonly string $projectId,
        private readonly string $documentType,
        private readonly array $fileMetadata,
    ) {
    }

    public function handle(DocumentAnalysisService $service): void
    {
        $service->analyze($this->documentId, $this->projectId, $this->documentType, $this->fileMetadata);
    }
}
