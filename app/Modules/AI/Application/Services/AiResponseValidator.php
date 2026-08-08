<?php

namespace App\Modules\AI\Application\Services;

use App\Modules\AI\Domain\Exceptions\InvalidAiPrecheckResponseException;
use App\Modules\AI\Domain\ValueObjects\AiRecommendation;

/**
 * AiResponseValidator (§4 of the Project Owner's AI-Compliance brief, WAJIB)
 *
 * Validates a raw provider response BEFORE it is ever wrapped in
 * AiPrecheckResult, persisted, or shown to a Compliance Officer. A response
 * that fails validation is never coerced or partially trusted — the caller
 * must treat it exactly like a registry-gate failure (safe fallback to
 * "unavailable", Scenario 3 in the Project Owner's test scenarios).
 *
 * Deliberately stricter than the Project Owner's own sketch: rejects
 * non-integer risk_score/confidence, unknown recommendation values, and
 * non-array issues/citations, not only an out-of-range risk_score.
 */
class AiResponseValidator
{
    /**
     * @param  array<string, mixed>  $response
     * @return array{risk_score: int, issues: array<int, string>, recommendation: AiRecommendation,
     *     confidence: int, citations: array<int, string>}
     *
     * @throws InvalidAiPrecheckResponseException
     */
    public function validate(array $response): array
    {
        if (! array_key_exists('risk_score', $response) || ! is_int($response['risk_score'])) {
            throw new InvalidAiPrecheckResponseException('AI response missing or non-integer risk_score.');
        }

        if ($response['risk_score'] < 0 || $response['risk_score'] > 100) {
            throw new InvalidAiPrecheckResponseException('AI response risk_score out of range (must be 0-100).');
        }

        if (! array_key_exists('confidence', $response) || ! is_int($response['confidence'])) {
            throw new InvalidAiPrecheckResponseException('AI response missing or non-integer confidence.');
        }

        if ($response['confidence'] < 0 || $response['confidence'] > 100) {
            throw new InvalidAiPrecheckResponseException('AI response confidence out of range (must be 0-100).');
        }

        if (! array_key_exists('recommendation', $response) || ! is_string($response['recommendation'])) {
            throw new InvalidAiPrecheckResponseException('AI response missing or non-string recommendation.');
        }

        $recommendation = AiRecommendation::tryFrom($response['recommendation']);

        if (! $recommendation) {
            throw new InvalidAiPrecheckResponseException(
                "AI response recommendation '{$response['recommendation']}' is not a recognized value "
                    . '(expected APPROVE, REJECT, REVIEW, or INSUFFICIENT_DATA).'
            );
        }

        $issues = $response['issues'] ?? [];
        $citations = $response['citations'] ?? [];

        if (! is_array($issues) || ! is_array($citations)) {
            throw new InvalidAiPrecheckResponseException('AI response issues/citations must be arrays.');
        }

        // WAJIB (Project Owner's prompt design, §3): a REVIEW/INSUFFICIENT_DATA
        // recommendation with zero citations is suspicious but not itself
        // invalid at this layer — citations completeness is a Prompt/§18
        // Evaluation Framework quality concern, not a hard schema violation.
        // Never invented here: an empty citations array is passed through as-is,
        // not backfilled with a fabricated citation.

        return [
            'risk_score' => $response['risk_score'],
            'issues' => array_values(array_map('strval', $issues)),
            'recommendation' => $recommendation,
            'confidence' => $response['confidence'],
            'citations' => array_values(array_map('strval', $citations)),
        ];
    }
}
