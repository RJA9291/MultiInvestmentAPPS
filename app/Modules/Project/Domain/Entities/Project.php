<?php

namespace App\Modules\Project\Domain\Entities;

use App\Modules\Project\Domain\ValueObjects\PublishState;
use DomainException;

/**
 * Project Aggregate Root (06_DOMAIN_MODEL.md §3.1)
 *
 * Per PDL-022, this Entity is solely responsible for protecting its own
 * invariants — no external code mutates its state directly; every mutation
 * goes through one of the transition methods below, which enforce
 * PublishState's allowed-transition table (Domain/ValueObjects/PublishState.php)
 * and raise a DomainException rather than silently allowing an invalid jump.
 */
class Project
{
    private function __construct(
        private readonly string $id,
        private readonly string $projectCode,
        private readonly string $ownerUserId,
        private string $title,
        private ?string $description,
        private ?string $category,
        private PublishState $status,
        private ?string $currentComplianceReviewId,
    ) {
    }

    public static function createDraft(
        string $id,
        string $projectCode,
        string $ownerUserId,
        string $title,
        ?string $description,
        ?string $category,
    ): self {
        return new self($id, $projectCode, $ownerUserId, $title, $description, $category, PublishState::Draft, null);
    }

    public static function reconstitute(
        string $id,
        string $projectCode,
        string $ownerUserId,
        string $title,
        ?string $description,
        ?string $category,
        PublishState $status,
        ?string $currentComplianceReviewId,
    ): self {
        return new self($id, $projectCode, $ownerUserId, $title, $description, $category, $status, $currentComplianceReviewId);
    }

    /** ProjectSubmitted: Draft -> Submitted (enters the Compliance Context queue). */
    public function submit(): void
    {
        $this->transitionTo(PublishState::Submitted);
    }

    /**
     * Internal, called only by the Application Service reacting to the
     * Compliance Module opening a review cycle — Submitted -> UnderComplianceReview.
     */
    public function enterComplianceReview(string $complianceReviewId): void
    {
        $this->transitionTo(PublishState::UnderComplianceReview);
        $this->currentComplianceReviewId = $complianceReviewId;
    }

    /**
     * ProjectApproved — the Investment Context's own reactive event, emitted
     * ONLY in direct response to consuming Compliance Context's
     * ProjectComplianceApproved (06_DOMAIN_MODEL.md §3.1's documented
     * Anti-Corruption Layer translation). Never called directly by a Controller.
     */
    public function markApproved(): void
    {
        $this->transitionTo(PublishState::Approved);
    }

    /**
     * ProjectReturnedToBusinessOwner — reactive, in response to Compliance
     * Context's ProjectComplianceRejected + ProjectReturnedToBusinessOwner.
     */
    public function markReturnedToBusinessOwner(): void
    {
        $this->transitionTo(PublishState::ReturnedToBusinessOwner);
    }

    /** ProjectResubmitted — the Business Owner's own action after edits. */
    public function resubmit(): void
    {
        $this->transitionTo(PublishState::Submitted);
    }

    /**
     * ProjectPublished — WAJIB gated by PublishEligibilityPolicy /
     * ComplianceReviewGatePolicy at the Service layer BEFORE this is called;
     * this method only enforces the state-machine transition itself.
     */
    public function publish(): void
    {
        $this->transitionTo(PublishState::Published);
    }

    public function unpublish(): void
    {
        $this->transitionTo(PublishState::Unpublished);
    }

    public function archive(): void
    {
        $this->transitionTo(PublishState::Archived);
    }

    private function transitionTo(PublishState $target): void
    {
        if (! $this->status->canTransitionTo($target)) {
            throw new DomainException(
                "Project {$this->id} cannot transition from {$this->status->value} to {$target->value}."
            );
        }

        $this->status = $target;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function projectCode(): string
    {
        return $this->projectCode;
    }

    public function ownerUserId(): string
    {
        return $this->ownerUserId;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function category(): ?string
    {
        return $this->category;
    }

    public function status(): PublishState
    {
        return $this->status;
    }

    public function currentComplianceReviewId(): ?string
    {
        return $this->currentComplianceReviewId;
    }
}
