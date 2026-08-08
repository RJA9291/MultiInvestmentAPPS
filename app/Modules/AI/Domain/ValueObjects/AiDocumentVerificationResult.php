<?php

namespace App\Modules\AI\Domain\ValueObjects;

/**
 * AiDocumentVerificationResult — the single source of truth for the
 * Document AI Verification response shape and for what gets persisted into
 * `ai_document_verifications` (DB-045). Mirrors AiPrecheckResult's shape
 * exactly (unavailable()/fromValidatedResponse()/fromPersisted()/toApiPayload()),
 * but is a DELIBERATELY DISTINCT type — not reused — because this
 * contract's fields differ from API-012's in two ways the Project Owner's
 * own brief specified:
 *
 *  - `recommendation` here is free-text advisory guidance (e.g. "Require
 *    resubmission before approval"), NOT the closed APPROVE/REJECT/REVIEW/
 *    INSUFFICIENT_DATA enum `AiRecommendation` uses for Compliance. Keeping
 *    these as two unrelated types (a string here, an enum there) preserves
 *    PDL-053's structural guarantee that nothing resembling an Approve/
 *    Reject decision can leak out of an advisory AI surface by accident.
 *  - `confidence` is a 0.00-1.00 float here (matching the brief's own 0.91
 *    example), not API-012's 0-100 integer scale — a new, isolated
 *    contract, not a reuse of AiResponseValidator's.
 */
final class AiDocumentVerificationResult
{
    private function __construct(
        public readonly bool $available,
        public readonly ?string $reason,
        public readonly ?int $completenessScore,
        public readonly array $issues,
        public readonly array $riskFlags,
        public readonly ?string $recommendation,
        public readonly ?float $confidence,
        public readonly array $citations,
        public readonly bool $aiUsed,
        public readonly ?string $promptCode,
        public readonly ?string $promptVersion,
        public readonly ?string $modelCode,
    ) {
    }

    public static function unavailable(?string $reason): self
    {
        return new self(
            available: false,
            reason: $reason,
            completenessScore: null,
            issues: [],
            riskFlags: [],
            recommendation: null,
            confidence: null,
            citations: [],
            aiUsed: false,
            promptCode: null,
            promptVersion: null,
            modelCode: null,
        );
    }

    /**
     * @param  array<int, string>  $issues
     * @param  array<int, string>  $riskFlags
     * @param  array<int, string>  $citations
     */
    public static function fromValidatedResponse(
        int $completenessScore,
        array $issues,
        array $riskFlags,
        string $recommendation,
        float $confidence,
        array $citations,
        string $promptCode,
        ?string $promptVersion,
        ?string $modelCode,
    ): self {
        return new self(
            available: true,
            reason: null,
            completenessScore: $completenessScore,
            issues: $issues,
            riskFlags: $riskFlags,
            recommendation: $recommendation,
            confidence: $confidence,
            citations: $citations,
            aiUsed: true,
            promptCode: $promptCode,
            promptVersion: $promptVersion,
            modelCode: $modelCode,
        );
    }

    /** Reconstructs from a persisted `ai_document_verifications` row. */
    public static function fromPersisted(
        int $completenessScore,
        array $issues,
        array $riskFlags,
        ?string $recommendation,
        ?float $confidence,
        array $citations,
        bool $aiUsed,
        ?string $promptCode,
        ?string $promptVersion,
        ?string $modelCode,
    ): self {
        return new self(
            available: true,
            reason: null,
            completenessScore: $completenessScore,
            issues: $issues,
            riskFlags: $riskFlags,
            recommendation: $recommendation,
            confidence: $confidence,
            citations: $citations,
            aiUsed: $aiUsed,
            promptCode: $promptCode,
            promptVersion: $promptVersion,
            modelCode: $modelCode,
        );
    }

    /**
     * PDL-058's literal text says "compliance outputs" — this extends the
     * same "AI Recommendation" label and PDL-053-style disclaimer here too,
     * for consistency with its spirit: this result is explicitly meant to
     * "help compliance make a decision" (Project Owner's brief §1), so it
     * gets the same non-negotiable labeling discipline. Flagged, not
     * silently assumed to be in scope of PDL-058's literal wording.
     */
    public function toApiPayload(): array
    {
        return [
            'label' => 'AI Recommendation',
            'available' => $this->available,
            'reason' => $this->reason,
            'completeness_score' => $this->completenessScore,
            'issues' => $this->issues,
            'risk_flags' => $this->riskFlags,
            'recommendation' => $this->recommendation,
            'confidence' => $this->confidence,
            'citations' => $this->citations,
            'disclaimer' => 'AI document verification is advisory only (PDL-053) and never replaces a '
                . 'Compliance Officer\'s review or decision. This result does not approve, reject, or '
                . 'change any document or project status by itself.',
        ];
    }
}
