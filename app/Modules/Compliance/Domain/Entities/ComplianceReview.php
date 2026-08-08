<?php

namespace App\Modules\Compliance\Domain\Entities;

use App\Modules\Compliance\Domain\ValueObjects\ComplianceStatus;
use DomainException;

/**
 * ComplianceReview Aggregate Root (06_DOMAIN_MODEL.md §10)
 *
 * One row per review CYCLE. PDL-027: once a decision is recorded (status
 * leaves Pending), this Entity refuses further mutation — a correction is
 * always a NEW ComplianceReview cycle, never an edit to this one.
 * PDL-028: never soft- or hard-deleted (enforced at the Repository/migration
 * level — no deleted_at column exists on compliance_reviews at all).
 */
class ComplianceReview
{
    /** @var array<int, string> */
    private array $comments = [];

    private function __construct(
        private readonly string $id,
        private readonly string $projectId,
        private readonly int $cycleNumber,
        private ?string $reviewerUserId,
        private ComplianceStatus $status,
        private ?string $decisionMadeBy = null,
        private ?string $decisionSource = null,
    ) {
    }

    public static function openNewCycle(string $id, string $projectId, int $cycleNumber): self
    {
        return new self($id, $projectId, $cycleNumber, null, ComplianceStatus::Pending);
    }

    public static function reconstitute(
        string $id,
        string $projectId,
        int $cycleNumber,
        ?string $reviewerUserId,
        ComplianceStatus $status,
        ?string $decisionMadeBy = null,
        ?string $decisionSource = null,
    ): self {
        return new self($id, $projectId, $cycleNumber, $reviewerUserId, $status, $decisionMadeBy, $decisionSource);
    }

    public function assignReviewer(string $reviewerUserId): void
    {
        $this->guardMutable();
        $this->reviewerUserId = $reviewerUserId;
    }

    public function addComment(string $comment): void
    {
        $this->guardMutable();
        $this->comments[] = $comment;
    }

    /**
     * WAJIB: caller (ComplianceDecisionService) must already have checked
     * ComplianceOfficerOnlyPolicy and, for a rejection, RejectionRequiresReasonPolicy
     * BEFORE calling this — this method only enforces PDL-027's immutability,
     * it does not re-check authorization or the reason requirement itself.
     *
     * $decisionSource (PDL-059) is provenance only ('HUMAN' default, or
     * 'AI_ASSISTED' if the officer consulted ComplianceAssistantService's
     * pre-check first). It carries NO authorization weight whatsoever — the
     * officer identified by $decidedByUserId is always the one who decided,
     * per ComplianceOfficerOnlyPolicy/BR-139/PDL-053, regardless of this value.
     */
    public function approve(string $decidedByUserId, string $decisionSource = 'HUMAN'): void
    {
        $this->guardMutable();
        $this->status = ComplianceStatus::Approved;
        $this->decisionMadeBy = $decidedByUserId;
        $this->decisionSource = $decisionSource;
    }

    public function reject(string $decidedByUserId, string $decisionSource = 'HUMAN'): void
    {
        $this->guardMutable();
        $this->status = ComplianceStatus::Rejected;
        $this->decisionMadeBy = $decidedByUserId;
        $this->decisionSource = $decisionSource;
    }

    private function guardMutable(): void
    {
        if ($this->status !== ComplianceStatus::Pending) {
            throw new DomainException(
                "ComplianceReview {$this->id} is already decided ({$this->status->value}) and is immutable (PDL-027)."
            );
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    public function projectId(): string
    {
        return $this->projectId;
    }

    public function cycleNumber(): int
    {
        return $this->cycleNumber;
    }

    public function reviewerUserId(): ?string
    {
        return $this->reviewerUserId;
    }

    public function status(): ComplianceStatus
    {
        return $this->status;
    }

    /** @return array<int, string> */
    public function pendingComments(): array
    {
        return $this->comments;
    }

    public function decisionMadeBy(): ?string
    {
        return $this->decisionMadeBy;
    }

    public function decisionSource(): ?string
    {
        return $this->decisionSource;
    }
}
