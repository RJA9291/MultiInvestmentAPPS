<?php

namespace App\Modules\AI\Application\Services;

use App\Modules\AI\Application\Contracts\AiProviderGatewayInterface;
use App\Modules\AI\Infrastructure\Registry\RegistryGate;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * DocumentAnalysisService (09_AI_ARCHITECTURE.md §20, Document Agent /
 * AGENT-008; PROMPT-004) — the AI-on-upload hook the Project Owner's
 * Document Module brief asked for ("RunDocumentAIAnalysisJob... check
 * missing fields / invalid format / compliance risk / fraud pattern").
 *
 * SCOPE DECISION (flagged): this mirrors ComplianceAssistantService's
 * RegistryGate -> Gateway -> fallback shape, but deliberately does NOT
 * build a full granular event chain (Requested/Completed events + a
 * dedicated ai_document_analysis_results table) the way the AI Compliance
 * Pre-check pipeline did. Nothing in the locked docs (06_DOMAIN_MODEL.md,
 * 07_EVENT_CATALOG.md, 08_DATABASE_DESIGN.md) defines a persisted AI
 * document-analysis result or its own events — unlike ai_compliance_results
 * (DB-044), which was explicitly added and locked for that purpose. Building
 * an equivalent table/event pair here would be inventing schema the Project
 * Owner has not reviewed. This is a single, queued, log-only analysis step:
 * proportionate to what's actually requested (an AI hook that runs and
 * reports, not a new audited decision record). If persisted, queryable
 * results are wanted later, that is a new brief — same pattern as
 * ai_compliance_results, added deliberately, not assumed here.
 *
 * WAJIB (PDL-041, PDL-050): checks the Registry for PROMPT-004 (Status =
 * Active, Owner assigned) before calling any provider. PROMPT-004 is seeded
 * as Draft (database/seeders/RegistrySeeder.php), so this correctly refuses
 * to run rather than fabricating a result — same honest-refusal pattern as
 * ComplianceAssistantService.
 *
 * AI NEVER BLOCKS (same Golden Rule as PDL-053's Compliance case, applied
 * here by extension): a Document has already been persisted and its
 * DocumentUploaded event already dispatched by the time this runs (it is a
 * reactive queued listener, not part of the upload transaction) — so a
 * Registry gate failure or a provider exception can only ever be logged,
 * never roll back or block the upload that already succeeded.
 */
class DocumentAnalysisService
{
    private const PROMPT_CODE = 'PROMPT-004';

    public function __construct(
        private readonly RegistryGate $registryGate,
        private readonly AiProviderGatewayInterface $gateway,
    ) {
    }

    /**
     * @param  array<string, mixed>  $fileMetadata  e.g. ['file_name' => ..., 'mime_type' => ...]
     */
    public function analyze(string $documentId, string $projectId, string $documentType, array $fileMetadata): void
    {
        $gate = $this->registryGate->checkPrompt(self::PROMPT_CODE);

        if (! $gate['active']) {
            Log::info('DocumentAnalysisService: skipped, registry not active.', [
                'document_id' => $documentId,
                'reason' => $gate['reason'],
            ]);

            return;
        }

        try {
            $promptVersion = $gate['prompt']?->version;

            $rawResponse = $this->gateway->analyze([
                'prompt_code' => self::PROMPT_CODE,
                'prompt_version' => $promptVersion,
                'document_id' => $documentId,
                'project_id' => $projectId,
                'document_type' => $documentType,
                'file_metadata' => $fileMetadata,
            ]);

            // No AiResponseValidator here (deliberately) — that validator's
            // shape (risk_score/recommendation enum/citations) is specific
            // to the Compliance Pre-check contract, API-012, never locked
            // for Document analysis. Raw provider output is logged as-is
            // once a real provider exists; NullAiProviderGateway currently
            // makes this branch unreachable in this build.
            Log::info('DocumentAnalysisService: analysis completed.', [
                'document_id' => $documentId,
                'model_code' => $this->gateway->modelCode(),
                'result' => $rawResponse,
            ]);
        } catch (Throwable $e) {
            Log::warning('DocumentAnalysisService: provider call failed, no impact on document status.', [
                'document_id' => $documentId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
