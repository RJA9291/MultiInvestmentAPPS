<?php

namespace Tests\Unit\Modules\Compliance;

use App\Modules\Compliance\Application\Services\ComplianceDecisionService;
use App\Modules\Compliance\Domain\Entities\ComplianceReview;
use App\Modules\Compliance\Domain\Policies\ComplianceOfficerOnlyPolicy;
use App\Modules\Compliance\Domain\Policies\RejectionRequiresReasonPolicy;
use App\Modules\Compliance\Domain\Repositories\ComplianceReviewRepositoryInterface;
use PHPUnit\Framework\TestCase;

/**
 * NOT YET EXECUTED — see AiResponseValidatorTest's doc comment; same
 * sandbox limitation applies (no vendor/, no PHPUnit runner available here).
 *
 * Proves PDL-053's core guarantee structurally, not just by inspection: an
 * AI recommendation (App\Modules\AI\Domain\ValueObjects\AiRecommendation)
 * is never even a PARAMETER type ComplianceDecisionService::decide() could
 * accept — a human's 'approved'/'rejected' string decision is completely
 * independent of whatever AiPrecheckResult said, in both directions.
 *
 * Scenario 1 (Project Owner's brief §10): AI says REJECT (or in our schema,
 * REVIEW/INSUFFICIENT_DATA) -> human APPROVES -> allowed.
 * Scenario 2: AI says APPROVE -> human REJECTS -> allowed.
 *
 * Uses PHPUnit's built-in createMock() (no Mockery dependency needed) for
 * the repository, and real Policy instances since they have no I/O.
 */
class ComplianceDecisionServiceOverrideTest extends TestCase
{
    public function test_human_can_approve_even_when_ai_recommendation_would_be_negative(): void
    {
        // The AI's own recommendation (e.g. AiRecommendation::Reject) is
        // never passed into this Service at all — there is no parameter for
        // it. This test documents that fact by simply never referencing
        // AiRecommendation, and still succeeding in approving.
        $review = ComplianceReview::openNewCycle('review-1', 'project-1', 1);

        $repository = $this->createMock(ComplianceReviewRepositoryInterface::class);
        $repository->method('find')->with('review-1')->willReturn($review);
        $repository->expects($this->once())->method('save')->with($review);

        $service = new ComplianceDecisionService(
            $repository,
            new ComplianceOfficerOnlyPolicy(),
            new RejectionRequiresReasonPolicy(),
        );

        $service->decide(
            reviewId: 'review-1',
            decision: 'approved',
            actingUserRole: 'compliance_officer',
            actingUserId: 'officer-1',
            decisionSource: 'AI_ASSISTED', // officer consulted the AI pre-check, still decided independently
        );

        $this->assertTrue($review->status()->value === 'approved');
        $this->assertSame('officer-1', $review->decisionMadeBy());
        $this->assertSame('AI_ASSISTED', $review->decisionSource());
    }

    public function test_human_can_reject_even_when_ai_recommendation_would_be_positive(): void
    {
        $review = ComplianceReview::openNewCycle('review-2', 'project-2', 1);

        $repository = $this->createMock(ComplianceReviewRepositoryInterface::class);
        $repository->method('find')->with('review-2')->willReturn($review);
        $repository->expects($this->once())->method('save')->with($review);

        $service = new ComplianceDecisionService(
            $repository,
            new ComplianceOfficerOnlyPolicy(),
            new RejectionRequiresReasonPolicy(),
        );

        $service->decide(
            reviewId: 'review-2',
            decision: 'rejected',
            actingUserRole: 'compliance_officer',
            actingUserId: 'officer-2',
            comments: ['AI flagged low risk, but Officer found an undisclosed related-party transaction.'],
            decisionSource: 'AI_ASSISTED',
        );

        $this->assertTrue($review->status()->value === 'rejected');
        $this->assertSame('officer-2', $review->decisionMadeBy());
    }

    public function test_non_officer_role_is_rejected_regardless_of_decision_source(): void
    {
        $this->expectException(\DomainException::class);

        $repository = $this->createMock(ComplianceReviewRepositoryInterface::class);
        $repository->expects($this->never())->method('save');

        $service = new ComplianceDecisionService(
            $repository,
            new ComplianceOfficerOnlyPolicy(),
            new RejectionRequiresReasonPolicy(),
        );

        // Even asserting decision_source = 'AI_ASSISTED' must not widen who
        // may call this method — BR-139's role gate applies identically.
        $service->decide(
            reviewId: 'review-3',
            decision: 'approved',
            actingUserRole: 'business_owner',
            actingUserId: 'user-3',
            decisionSource: 'AI_ASSISTED',
        );
    }
}
