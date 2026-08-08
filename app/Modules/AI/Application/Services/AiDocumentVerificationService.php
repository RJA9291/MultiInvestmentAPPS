<?php

namespace App\Modules\AI\Application\Services;

use App\Modules\AI\Application\Contracts\AiProviderGatewayInterface;
use App\Modules\AI\Application\Contracts\DocumentContentExtractorInterface;
use App\Modules\AI\Domain\Exceptions\InvalidAiDocumentVerificationResponseException;
use App\Modules\AI\Domain\Repositories\AiDocumentVerificationResultRepositoryInterface;
use App\Modules\AI\Domain\ValueObjects\AiDocumentVerificationResult;
use App\Modules\AI\Infrastructure\Registry\RegistryGate;
use App\Modules\Document\Domain\Repositories\DocumentRepositoryInterface;
use App\Modules\Document\Infrastructure\Storage\FileStorageGatewayInterface;
use Throwable;

/**
 * AiDocumentVerificationService (09_AI_ARCHITECTURE.md §20, Document Agent /
 * AGENT-008; PROMPT-004) — orchestrates the Project Owner's "AI Document
 * Verification Module" brief: RegistryGate -> fetch Document (interface-only,
 * PDL-020) -> extract+sanitize content -> AiProviderGateway ->
 * AiDocumentVerificationResponseValidator -> AiDocumentVerificationResult ->
 * AiDocumentVerificationResultRepository.
 *
 * SUPERSEDES the simpler single-listener hook built in v3.22.0
 * (DocumentAnalysisService/RunDocumentAnalysisJob/RunDocumentAnalysisOnUpload)
 * now that the Project Owner has specified the full granular pipeline those
 * files' own doc comments said would justify upgrading. Those files are left
 * in place, unregistered, with a doc comment explaining the supersession —
 * same "supersede in place, never delete" pattern already used for
 * RunAiCompliancePrecheckOnSubmit.
 *
 * NON-NEGOTIABLE (PDL-053, by the same extension already applied to
 * ComplianceAssistantService): this class produces an ADVISORY result only.
 * It has no dependency on ComplianceDecisionService or on Document::markApproved()
 * — an AI Document Verification never approves, rejects, or mutates a
 * Document's or Project's status by itself (Project Owner's brief, Core
 * Principle: "AI = ADVISOR ONLY, Human = DECISION MAKER").
 *
 * SECURITY (Project Owner's brief §12, WAJIB): document content is UNTRUSTED
 * INPUT. Before being sent to any provider it is stripped of control
 * characters and capped at MAX_CONTENT_LENGTH — a basic hygiene stopgap,
 * not a full prompt-injection defense; a real defense-in-depth pipeline is
 * `09_AI_ARCHITECTURE.md`'s own already-documented security pipeline, not
 * re-invented here. Raw extracted content is never returned in the result
 * payload or logged — only the validated, structured fields
 * (completeness_score/issues/risk_flags/recommendation/confidence/citations)
 * ever leave this class (§11 of the Project Owner's brief: "no raw output
 * display").
 */
class AiDocumentVerificationService
{
    private const PROMPT_CODE = 'PROMPT-004';

    private const MAX_CONTENT_LENGTH = 20000;

    public function __construct(
        private readonly RegistryGate $registryGate,
        private readonly AiProviderGatewayInterface $gateway,
        private readonly AiDocumentVerificationResponseValidator $validator,
        private readonly AiDocumentVerificationResultRepositoryInterface $results,
        private readonly DocumentRepositoryInterface $documents,
        private readonly FileStorageGatewayInterface $storage,
        private readonly DocumentContentExtractorInterface $extractor,
    ) {
    }

    public function verify(string $documentId, string $projectId): AiDocumentVerificationResult
    {
        $gate = $this->registryGate->checkPrompt(self::PROMPT_CODE);

        if (! $gate['active']) {
            return AiDocumentVerificationResult::unavailable($gate['reason']);
        }

        $document = $this->documents->find($documentId);

        if (! $document) {
            // Not a provider failure — the document itself is gone (e.g. deleted
            // between upload and this queued job running). Same "unavailable,
            // never block the workflow" treatment as any other failure mode.
            return AiDocumentVerificationResult::unavailable("Document {$documentId} not found.");
        }

        try {
            $attachment = $document->currentAttachment();
            $rawBytes = $this->storage->retrieve($attachment);
            $content = $this->sanitize($this->extractor->extract($attachment, $rawBytes));

            $extractionGapIssue = $content === '' && strlen($rawBytes) > 0
                ? ['Content extraction not supported for this file type in this build — analysis is based on metadata only.']
                : [];

            $promptVersion = $gate['prompt']?->version;

            $rawResponse = $this->gateway->analyze([
                'prompt_code' => self::PROMPT_CODE,
                'prompt_version' => $promptVersion,
                'document_id' => $documentId,
                'project_id' => $projectId,
                'document_type' => $document->documentType(),
                'file_name' => $attachment->fileName,
                'mime_type' => $attachment->mimeType,
                'content' => $content,
            ]);

            $validated = $this->validator->validate($rawResponse);

            $result = AiDocumentVerificationResult::fromValidatedResponse(
                completenessScore: $validated['completeness_score'],
                issues: array_merge($validated['issues'], $extractionGapIssue),
                riskFlags: $validated['risk_flags'],
                recommendation: $validated['recommendation'],
                confidence: $validated['confidence'],
                citations: $validated['citations'],
                promptCode: self::PROMPT_CODE,
                promptVersion: $promptVersion,
                modelCode: $this->gateway->modelCode(),
            );

            $this->results->save($documentId, $result);

            return $result;
        } catch (InvalidAiDocumentVerificationResponseException|Throwable $e) {
            // §11 WAJIB: "AI failure MUST NOT block system" — nothing is
            // persisted for a failed run; the prior ACTIVE result (if any)
            // remains the latest one findLatestActiveForDocument() returns.
            return AiDocumentVerificationResult::unavailable(
                'AI document verification failed and fell back to manual review: ' . $e->getMessage()
            );
        }
    }

    /** §12 WAJIB: strip control characters and cap length before any content leaves this process to a provider. */
    private function sanitize(string $content): string
    {
        $stripped = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $content) ?? '';

        return mb_substr($stripped, 0, self::MAX_CONTENT_LENGTH);
    }
}
