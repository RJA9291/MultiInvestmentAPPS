<?php

namespace App\Modules\AI\Application\Services;

use App\Modules\AI\Domain\Exceptions\InvalidAiDocumentVerificationResponseException;

/**
 * AiDocumentVerificationResponseValidator — mirrors AiResponseValidator's
 * strictness discipline, but for the Document Verification contract's own
 * shape: `completeness_score` (int 0-100), `issues`/`risk_flags`/`citations`
 * (arrays), `recommendation` (non-empty free-text string — NOT validated
 * against AiRecommendation's enum, deliberately, per
 * AiDocumentVerificationResult's own doc comment), `confidence` (float
 * 0.0-1.0, matching the Project Owner's own 0.91 example — a different
 * scale from API-012's 0-100 integer confidence).
 */
class AiDocumentVerificationResponseValidator
{
    /**
     * @param  array<string, mixed>  $response
     * @return array{completeness_score: int, issues: array<int, string>, risk_flags: array<int, string>,
     *     recommendation: string, confidence: float, citations: array<int, string>}
     *
     * @throws InvalidAiDocumentVerificationResponseException
     */
    public function validate(array $response): array
    {
        if (! array_key_exists('completeness_score', $response) || ! is_int($response['completeness_score'])) {
            throw new InvalidAiDocumentVerificationResponseException('AI response missing or non-integer completeness_score.');
        }

        if ($response['completeness_score'] < 0 || $response['completeness_score'] > 100) {
            throw new InvalidAiDocumentVerificationResponseException('AI response completeness_score out of range (must be 0-100).');
        }

        if (! array_key_exists('confidence', $response) || ! is_numeric($response['confidence'])) {
            throw new InvalidAiDocumentVerificationResponseException('AI response missing or non-numeric confidence.');
        }

        $confidence = (float) $response['confidence'];

        if ($confidence < 0.0 || $confidence > 1.0) {
            throw new InvalidAiDocumentVerificationResponseException('AI response confidence out of range (must be 0.0-1.0).');
        }

        if (! array_key_exists('recommendation', $response) || ! is_string($response['recommendation']) || trim($response['recommendation']) === '') {
            throw new InvalidAiDocumentVerificationResponseException('AI response missing or empty recommendation.');
        }

        $issues = $response['issues'] ?? [];
        $riskFlags = $response['risk_flags'] ?? [];
        $citations = $response['citations'] ?? [];

        if (! is_array($issues) || ! is_array($riskFlags) || ! is_array($citations)) {
            throw new InvalidAiDocumentVerificationResponseException('AI response issues/risk_flags/citations must be arrays.');
        }

        return [
            'completeness_score' => $response['completeness_score'],
            'issues' => array_values(array_map('strval', $issues)),
            'risk_flags' => array_values(array_map('strval', $riskFlags)),
            'recommendation' => trim($response['recommendation']),
            'confidence' => $confidence,
            'citations' => array_values(array_map('strval', $citations)),
        ];
    }
}
