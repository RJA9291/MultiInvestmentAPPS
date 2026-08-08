<?php

namespace App\Modules\Document\Domain\Entities;

use App\Modules\Document\Domain\ValueObjects\Attachment;
use Carbon\CarbonInterface;

/** DocumentVersion Entity (06_DOMAIN_MODEL.md §3.2, DB-007) — immutable once created. */
class DocumentVersion
{
    private function __construct(
        private readonly string $id,
        private readonly string $documentId,
        private readonly int $versionNumber,
        private readonly Attachment $attachment,
        private readonly string $createdBy,
        private readonly CarbonInterface $createdAt,
    ) {
    }

    public static function create(
        string $id,
        string $documentId,
        int $versionNumber,
        Attachment $attachment,
        string $createdBy,
    ): self {
        return new self($id, $documentId, $versionNumber, $attachment, $createdBy, now());
    }

    public static function reconstitute(
        string $id,
        string $documentId,
        int $versionNumber,
        Attachment $attachment,
        string $createdBy,
        CarbonInterface $createdAt,
    ): self {
        return new self($id, $documentId, $versionNumber, $attachment, $createdBy, $createdAt);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function documentId(): string
    {
        return $this->documentId;
    }

    public function versionNumber(): int
    {
        return $this->versionNumber;
    }

    public function attachment(): Attachment
    {
        return $this->attachment;
    }

    public function createdBy(): string
    {
        return $this->createdBy;
    }

    public function createdAt(): CarbonInterface
    {
        return $this->createdAt;
    }
}
