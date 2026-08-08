<?php

namespace App\Modules\Compliance\Application\Services;

use App\Modules\Compliance\Domain\Entities\ComplianceReview;
use App\Modules\Compliance\Domain\Events\ComplianceReviewStarted;
use App\Modules\Compliance\Domain\Repositories\ComplianceReviewRepositoryInterface;
use Illuminate\Support\Str;

/**
 * ComplianceReviewService — orchestrates opening a review cycle.
 * Called by Application/Listeners/OpenComplianceReviewCycle in reaction to
 * Project Module's ProjectSubmitted/ProjectResubmitted (06_DOMAIN_MODEL.md
 * §11's Context Map: "Investment -> Compliance, Customer/Supplier via Domain Event").
 *
 * Dispatches ComplianceReviewStarted from here, not from the AI Module's
 * event chain — a new cycle opening is authoritative the moment this method
 * completes, regardless of whether an AI pre-check has run yet. See
 * ComplianceReviewStarted's own doc comment for why this independence
 * matters (Test Scenario 3 / "AI failure != system failure").
 */
class ComplianceReviewService
{
    public function __construct(private readonly ComplianceReviewRepositoryInterface $reviews)
    {
    }

    public function openCycle(string $projectId): ComplianceReview
    {
        // PDL-027: a rejected-and-resubmitted Project gets a NEW row, never an
        // edit to a prior cycle. Determine the next cycle number defensively.
        $priorActive = $this->reviews->findActiveCycleForProject($projectId);
        $cycleNumber = $priorActive ? $priorActive->cycleNumber() + 1 : 1;

        $review = ComplianceReview::openNewCycle(
            id: (string) Str::uuid(),
            projectId: $projectId,
            cycleNumber: $cycleNumber,
        );

        $this->reviews->save($review);

        ComplianceReviewStarted::dispatch($review->id(), $review->projectId());

        return $review;
    }
}
