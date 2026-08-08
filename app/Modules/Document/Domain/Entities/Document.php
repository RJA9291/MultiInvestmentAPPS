<?php

namespace App\Modules\Document\Domain\Entities;

use App\Modules\Document\Domain\ValueObjects\Attachment;
use Carbon\CarbonInterface;
use DomainException;

/**
 * Document Aggregate Root (06_DOMAIN_MODEL.md §3.2, DB-006)
 *
 * Represents ONE logical document's current state — its version history
 * lives in the separate DocumentVersion Entity/table (DB-007), never
 * inlined here as a single incrementing integer.
 */
class Document
{
    private function __construct(
        private readonly string $id,
        private readonly string $projectId,
        private readonly string $documentType,
        private Attachment $currentAttachment,
        private bool $isApproved,
        private readonly string $uploadedBy,
        private readonly CarbonInterface $uploadedAt,
        private ?string $approvedBy,
        private ?CarbonInterface $approvedAt,
    ) {
    }

    public static function upload(
        string $id,
        string $projectId,
        string $documentType,
        Attachment $attachment,
        string $uploadedBy,
    ): self {
        return new self($id, $projectId, $documentType, $attachment, false, $uploadedBy, now(), null, null);
    }

    public static function reconstitute(
        string $id,
        string $projectId,
        string $documentType,
        Attachment $currentAttachment,
        bool $isApproved,
        string $uploadedBy,
        CarbonInterface $uploadedAt,
        ?string $approvedBy,
        ?CarbonInterface $approvedAt,
    ): self {
        return new self($id, $projectId, $documentType, $currentAttachment, $isApproved, $uploadedBy, $uploadedAt, $approvedBy, $approvedAt);
    }

    /**
     * BR-017: eligible to count toward PublishEligibilityPolicy once
     * approved. Represents an automated ingest-check pass (malware/type/
     * completeness), not a human review decision — see DocumentApproved's
     * own doc comment.
     */
    public function markApproved(string $approvedBy): void
    {
        if ($this->isApproved) {
            throw new DomainException("Document {$this->id} is already approved.");
        }

        $this->isApproved = true;
        $this->approvedBy = $approvedBy;
        $this->approvedAt = now();
    }

    /**
     * Called after a new DocumentVersion is created (DocumentVersioningService)
     * to keep this Aggregate's "current" Attachment pointer in sync. Does
     * NOT reset `isApproved` — whether a new version should require
     * re-approval is a real product question the Project Owner has not
     * specified; left as-is (approval sticks) rather than guessed either way.
     */
    public function attachNewVersion(Attachment $attachment): void
    {
        $this->currentAttachment = $attachment;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function projectId(): string
    {
        return $this->projectId;
    }

    public function documentType(): string
    {
        return $this->documentType;
    }

    public function currentAttachment(): Attachment
    {
        return $this->currentAttachment;
    }

    public function isApproved(): bool
    {
        return $this->isApproved;
    }

    public function uploadedBy(): string
    {
        return $this->uploadedBy;
    }

    public function uploadedAt(): CarbonInterface
    {
        return $this->uploadedAt;
    }

    public function approvedBy(): ?string
    {
        return $this->approvedBy;
    }

    public function approvedAt(): ?CarbonInterface
    {
        return $this->approvedAt;
    }
}
