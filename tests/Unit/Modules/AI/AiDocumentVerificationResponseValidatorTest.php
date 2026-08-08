<?php

namespace Tests\Unit\Modules\AI;

use App\Modules\AI\Application\Services\AiDocumentVerificationResponseValidator;
use App\Modules\AI\Domain\Exceptions\InvalidAiDocumentVerificationResponseException;
use PHPUnit\Framework\TestCase;

/**
 * NOT YET EXECUTED — this sandbox has no PHP/Composer/PHPUnit installed
 * (no vendor/ directory), so this file has been hand-written and manually
 * reviewed only. Run via `vendor/bin/phpunit` once `composer install` has
 * been run locally. Mirrors AiResponseValidatorTest's structure for the
 * Document Verification contract's own shape (completeness_score/risk_flags,
 * free-text recommendation, 0.0-1.0 confidence).
 */
class AiDocumentVerificationResponseValidatorTest extends TestCase
{
    private AiDocumentVerificationResponseValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new AiDocumentVerificationResponseValidator();
    }

    public function test_it_accepts_a_well_formed_response(): void
    {
        $result = $this->validator->validate([
            'completeness_score' => 82,
            'issues' => ['Missing auditor signature', 'No date on page 4'],
            'risk_flags' => ['Financial inconsistency detected'],
            'recommendation' => 'Require resubmission before approval',
            'confidence' => 0.91,
            'citations' => ['Page 4', 'Section 2.1'],
        ]);

        $this->assertSame(82, $result['completeness_score']);
        $this->assertSame(['Missing auditor signature', 'No date on page 4'], $result['issues']);
        $this->assertSame(['Financial inconsistency detected'], $result['risk_flags']);
        $this->assertSame('Require resubmission before approval', $result['recommendation']);
        $this->assertSame(0.91, $result['confidence']);
        $this->assertSame(['Page 4', 'Section 2.1'], $result['citations']);
    }

    public function test_it_accepts_a_clean_document_with_empty_issues_and_risk_flags(): void
    {
        $result = $this->validator->validate([
            'completeness_score' => 100,
            'recommendation' => 'Approve for compliance review',
            'confidence' => 1.0,
        ]);

        $this->assertSame([], $result['issues']);
        $this->assertSame([], $result['risk_flags']);
        $this->assertSame([], $result['citations']);
    }

    public function test_it_rejects_missing_completeness_score(): void
    {
        $this->expectException(InvalidAiDocumentVerificationResponseException::class);

        $this->validator->validate([
            'recommendation' => 'Require resubmission',
            'confidence' => 0.5,
        ]);
    }

    public function test_it_rejects_out_of_range_completeness_score(): void
    {
        $this->expectException(InvalidAiDocumentVerificationResponseException::class);

        $this->validator->validate([
            'completeness_score' => 101,
            'recommendation' => 'Require resubmission',
            'confidence' => 0.5,
        ]);
    }

    public function test_it_rejects_confidence_above_one(): void
    {
        // Guards against a provider accidentally returning a 0-100 scale
        // (API-012's contract) into this 0.0-1.0 contract by mistake.
        $this->expectException(InvalidAiDocumentVerificationResponseException::class);

        $this->validator->validate([
            'completeness_score' => 82,
            'recommendation' => 'Require resubmission',
            'confidence' => 91, // out of 0.0-1.0 range
        ]);
    }

    public function test_it_rejects_empty_recommendation(): void
    {
        $this->expectException(InvalidAiDocumentVerificationResponseException::class);

        $this->validator->validate([
            'completeness_score' => 82,
            'recommendation' => '   ',
            'confidence' => 0.5,
        ]);
    }

    public function test_it_rejects_non_array_risk_flags(): void
    {
        $this->expectException(InvalidAiDocumentVerificationResponseException::class);

        $this->validator->validate([
            'completeness_score' => 82,
            'recommendation' => 'Require resubmission',
            'confidence' => 0.5,
            'risk_flags' => 'not-an-array',
        ]);
    }
}
