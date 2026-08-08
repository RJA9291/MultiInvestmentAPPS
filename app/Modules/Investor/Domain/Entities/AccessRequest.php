<?php

namespace App\Modules\Investor\Domain\Entities;

use App\Modules\Investor\Domain\ValueObjects\AccessRequestStatus;
use Carbon\CarbonInterface;
use DomainException;

/**
 * AccessRequest Aggregate Root (Investment Context — new this Sprint).
 *
 * An Investor's request for Data Room access to a Project. Deliberately
 * per-PROJECT, unlike DataRoomGrant (DB-008) which is per-DOCUMENT — see
 * ApproveAccessRequestService's doc comment for how approval fans a single
 * AccessRequest out into one DataRoomGrant per currently-approved Document.
 *
 * `status` is immutable once decided — same discipline as
 * `compliance_reviews` (BR-140/PDL-027), applied here as a new rule
 * (see 04_BUSINESS_RULES.md's new entry for this Aggregate).
 */
class AccessRequest
{
    private function __construct(
        private readonly string $id,
        private readonly string $projectId,
        private readonly string $investorUserId,
        private AccessRequestStatus $status,
        private readonly CarbonInterface $requestedAt,
        private ?string $decidedBy,
        private ?CarbonInterface $decidedAt,
    ) {
    }

    public static function request(string $id, string $projectId, string $investorUserId): self
    {
        return new self($id, $projectId, $investorUserId, AccessRequestStatus::Pending, now(), null, null);
    }

    public static function reconstitute(
        string $id,
        string $projectId,
        string $investorUserId,
        AccessRequestStatus $status,
        CarbonInterface $requestedAt,
        ?string $decidedBy,
        ?CarbonInterface $decidedAt,
    ): self {
        return new self($id, $projectId, $investorUserId, $status, $requestedAt, $decidedBy, $decidedAt);
    }

    public function approve(string $decidedBy): void
    {
        $this->assertPending();

        $this->status = AccessRequestStatus::Approved;
        $this->decidedBy = $decidedBy;
        $this->decidedAt = now();
    }

    public function reject(string $decidedBy): void
    {
        $this->assertPending();

        $this->status = AccessRequestStatus::Rejected;
        $this->decidedBy = $decidedBy;
        $this->decidedAt = now();
    }

    private function assertPending(): void
    {
        if ($this->status !== AccessRequestStatus::Pending) {
            throw new DomainException("Access request {$this->id} has already been decided ({$this->status->value}).");
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

    public function investorUserId(): string
    {
        return $this->investorUserId;
    }

    public function status(): AccessRequestStatus
    {
        return $this->status;
    }

    public function requestedAt(): CarbonInterface
    {
        return $this->requestedAt;
    }

    public function decidedBy(): ?string
    {
        return $this->decidedBy;
    }

    public function decidedAt(): ?CarbonInterface
    {
        return $this->decidedAt;
    }
}
