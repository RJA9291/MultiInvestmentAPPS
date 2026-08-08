<?php

namespace App\Modules\Compliance\Application\Services;

use App\Modules\Compliance\Domain\Events\ProjectComplianceApproved;
use App\Modules\Compliance\Domain\Events\ProjectComplianceRejected;
use App\Modules\Compliance\Domain\Events\ProjectReturnedToBusinessOwner;
use App\Modules\Compliance\Domain\Policies\ComplianceOfficerOnlyPolicy;
use App\Modules\Compliance\Domain\Policies\RejectionRequiresReasonPolicy;
use App\Modules\Compliance\Domain\Repositories\ComplianceReviewRepositoryInterface;
use DomainException;
use RuntimeException;

/**
 * ComplianceDecisionService — records an immutable decision (BR-139, BR-140,
 * BR-141, PDL-027). This is the ONLY place a ComplianceReview's status may
 * move out of Pending.
 *
 * WAJIB, non-negotiable (09_AI_ARCHITECTURE.md §15/§20, BR-139): this method
 * is never called by AI code. The AI Compliance pre-check
 * (Modules/AI/Application/Services/ComplianceAssistantService.php) surfaces
 * advisory flags to a human reviewer — it has no path into this class.
 */
class ComplianceDecisionService
{
    public function __construct(
        private readonly ComplianceReviewRepositoryInterface $reviews,
        private readonly ComplianceOfficerOnlyPolicy $officerOnlyPolicy,
        private readonly RejectionRequiresReasonPolicy $rejectionRequiresReasonPolicy,
    ) {
    }

    /**
     * @param  string  $actingUserRole  FLAGGED: passed explicitly by the caller
     *         until Identity Module's real AuthorizationGate exists
     *         (Domain/Policies/ComplianceOfficerOnlyPolicy.php's own flag).
     * @param  string  $actingUserId  The deciding Compliance Officer's user ID —
     *         persisted as `decision_made_by` (PDL-059).
     * @param  string  $decisionSource  'HUMAN' (default) or 'AI_ASSISTED' — PDL-059
     *         provenance only. Carries no authorization weight: this method's
     *         $actingUserRole gate above applies identically either way. Passing
     *         'AI_ASSISTED' never means AI decided; it only records that the
     *         officer consulted ComplianceAssistantService's pre-check first.
     * @param  array<int, string>  $comments  Required (>= 1) when $decision === 'rejected' (BR-141).
     */
    public function decide(
        string $reviewId,
        string $decision,
        string $actingUserRole,
        string $actingUserId,
        array $comments = [],
        string $decisionSource = 'HUMAN',
    ): void {
        if (! $this->officerOnlyPolicy->isAuthorized($actingUserRole)) {
            throw new DomainException('Only the Compliance Officer role may record a compliance decision (BR-139).');
        }

        $review = $this->reviews->find($reviewId);

        if (! $review) {
            throw new RuntimeException("ComplianceReview {$reviewId} not found.");
        }

        foreach ($comments as $comment) {
            $review->addComment($comment);
        }

        if ($decision === 'rejected') {
            if (! $this->rejectionRequiresReasonPolicy->isSatisfied($review->pendingComments())) {
                throw new DomainException('A rejection requires at least one comment (BR-141).');
            }

            $review->reject($actingUserId, $decisionSource);
            $this->reviews->save($review);

            ProjectComplianceRejected::dispatch($review->id(), $review->projectId());
            ProjectReturnedToBusinessOwner::dispatch($review->id(), $review->projectId());

            return;
        }

        if ($decision === 'approved') {
            $review->approve($actingUserId, $decisionSource);
            $this->reviews->save($review);

            ProjectComplianceApproved::dispatch($review->id(), $review->projectId());

            return;
        }

        throw new DomainException("Unknown compliance decision '{$decision}'; expected 'approved' or 'rejected'.");
    }
}
