<?php

namespace App\Modules\AI\Domain\ValueObjects;

/**
 * AiPrecheckResult — the single source of truth for API-012's response
 * shape (12_API_STANDARD.md v1.2.0 §15) and for what gets persisted into
 * `ai_compliance_results` (DB-044). Centralizing serialization here means
 * the PDL-058 "AI Recommendation" label and the PDL-053 disclaimer are
 * written in exactly one place — no Controller or Service can forget them
 * by constructing a bare array by hand.
 */
final class AiPrecheckResult
{
    private function __construct(
        public readonly bool $available,
        public readonly ?string $reason,
        public readonly ?int $riskScore,
        public readonly array $issuesDetected,
        public readonly ?AiRecommendation $recommendation,
        public readonly ?int $confidence,
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
            riskScore: null,
            issuesDetected: [],
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
     * @param  array<int, string>  $issuesDetected
     * @param  array<int, string>  $citations
     */
    public static function fromValidatedResponse(
        int $riskScore,
        array $issuesDetected,
        AiRecommendation $recommendation,
        int $confidence,
        array $citations,
        string $promptCode,
        ?string $promptVersion,
        ?string $modelCode,
    ): self {
        return new self(
            available: true,
            reason: null,
            riskScore: $riskScore,
            issuesDetected: $issuesDetected,
            recommendation: $recommendation,
            confidence: $confidence,
            citations: $citations,
            aiUsed: true,
            promptCode: $promptCode,
            promptVersion: $promptVersion,
            modelCode: $modelCode,
        );
    }

    /**
     * Reconstructs from a persisted `ai_compliance_results` row — used when
     * API-012 serves a previously-stored result rather than running a fresh
     * pre-check (see ComplianceAssistantPreCheckController).
     */
    public static function fromPersisted(
        int $riskScore,
        array $issuesDetected,
        string $recommendation,
        int $confidence,
        array $citations,
        bool $aiUsed,
        ?string $promptCode,
        ?string $promptVersion,
        ?string $modelCode,
    ): self {
        return new self(
            available: true,
            reason: null,
            riskScore: $riskScore,
            issuesDetected: $issuesDetected,
            recommendation: AiRecommendation::from($recommendation),
            confidence: $confidence,
            citations: $citations,
            aiUsed: $aiUsed,
            promptCode: $promptCode,
            promptVersion: $promptVersion,
            modelCode: $modelCode,
        );
    }

    /**
     * Locked API-012 response contract (12_API_STANDARD.md v1.2.0 §15):
     * risk_score, issues_detected, recommendation, confidence, citations,
     * plus the mandatory `label` (PDL-058) and `disclaimer` (PDL-053).
     * This is the ONLY method in the codebase that should ever produce the
     * `data` payload for API-012 — Controllers must call this, never hand-roll it.
     */
    public function toApiPayload(): array
    {
        return [
            'label' => 'AI Recommendation', // PDL-058 — mandatory, never omitted
            'available' => $this->available,
            'reason' => $this->reason,
            'risk_score' => $this->riskScore,
            'issues_detected' => $this->issuesDetected,
            'recommendation' => $this->recommendation?->value,
            'confidence' => $this->confidence,
            'citations' => $this->citations,
            'disclaimer' => 'AI compliance pre-check is advisory only (PDL-053) and never replaces the '
                . 'Compliance Officer\'s decision (BR-139). This endpoint (API-012) never triggers, queues, '
                . 'or otherwise causes the Approval/Rejection flow.',
        ];
    }
}
