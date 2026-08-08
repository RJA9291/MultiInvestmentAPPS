<?php

namespace Tests\Unit\Modules\AI;

use App\Modules\AI\Application\Services\AiResponseValidator;
use App\Modules\AI\Domain\Exceptions\InvalidAiPrecheckResponseException;
use App\Modules\AI\Domain\ValueObjects\AiRecommendation;
use PHPUnit\Framework\TestCase;

/**
 * NOT YET EXECUTED — this sandbox has no PHP/Composer/PHPUnit installed
 * (no vendor/ directory), so this file has been hand-written and manually
 * reviewed only. Run via `vendor/bin/phpunit` or `php artisan test` once
 * `composer install` has been run locally (14_LARAVEL_BLUEPRINT.md §18
 * Testing Strategy).
 *
 * Pure unit test — no Laravel bootstrap, no DB, extends PHPUnit's own
 * TestCase directly, matching AiResponseValidator having no framework
 * dependency itself.
 */
class AiResponseValidatorTest extends TestCase
{
    private AiResponseValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new AiResponseValidator();
    }

    public function test_it_accepts_a_well_formed_response(): void
    {
        $result = $this->validator->validate([
            'risk_score' => 72,
            'issues' => ['Missing financial statement'],
            'recommendation' => 'REVIEW',
            'confidence' => 88,
            'citations' => ['Document A', 'Clause 5'],
        ]);

        $this->assertSame(72, $result['risk_score']);
        $this->assertSame(AiRecommendation::Review, $result['recommendation']);
        $this->assertSame(88, $result['confidence']);
        $this->assertSame(['Missing financial statement'], $result['issues']);
        $this->assertSame(['Document A', 'Clause 5'], $result['citations']);
    }

    public function test_it_accepts_insufficient_data_with_empty_issues_and_citations(): void
    {
        $result = $this->validator->validate([
            'risk_score' => 0,
            'recommendation' => 'INSUFFICIENT_DATA',
            'confidence' => 0,
        ]);

        $this->assertSame(AiRecommendation::InsufficientData, $result['recommendation']);
        $this->assertSame([], $result['issues']);
        $this->assertSame([], $result['citations']);
    }

    public function test_it_rejects_missing_risk_score(): void
    {
        $this->expectException(InvalidAiPrecheckResponseException::class);

        $this->validator->validate([
            'recommendation' => 'REVIEW',
            'confidence' => 50,
        ]);
    }

    public function test_it_rejects_out_of_range_risk_score(): void
    {
        $this->expectException(InvalidAiPrecheckResponseException::class);

        $this->validator->validate([
            'risk_score' => 150,
            'recommendation' => 'REVIEW',
            'confidence' => 50,
        ]);
    }

    public function test_it_rejects_out_of_range_confidence(): void
    {
        $this->expectException(InvalidAiPrecheckResponseException::class);

        $this->validator->validate([
            'risk_score' => 50,
            'recommendation' => 'REVIEW',
            'confidence' => -1,
        ]);
    }

    public function test_it_rejects_an_unrecognized_recommendation_value(): void
    {
        $this->expectException(InvalidAiPrecheckResponseException::class);

        // Deliberately tests that this validator does NOT accept
        // ComplianceStatus's vocabulary ('approved'/'rejected') even though
        // it looks superficially similar — PDL-053's type separation must
        // hold at the validation boundary too, not just at the type level.
        $this->validator->validate([
            'risk_score' => 50,
            'recommendation' => 'approved',
            'confidence' => 50,
        ]);
    }

    public function test_it_rejects_non_array_issues(): void
    {
        $this->expectException(InvalidAiPrecheckResponseException::class);

        $this->validator->validate([
            'risk_score' => 50,
            'recommendation' => 'REVIEW',
            'confidence' => 50,
            'issues' => 'not an array',
        ]);
    }
}
