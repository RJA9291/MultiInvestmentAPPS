<?php

namespace App\Modules\DataRoom\Domain\Entities;

use App\Modules\DataRoom\Domain\ValueObjects\PermissionTier;
use Carbon\CarbonInterface;
use DomainException;

/**
 * DataRoomGrant Aggregate Root (06_DOMAIN_MODEL.md §3.3, ADR-002)
 *
 * One per document-per-authorized-user (DB-008's Business Key). Built with
 * the same private-constructor + static-factory + guarded-mutation shape as
 * Project/ComplianceReview, NOT the Project Owner's sketch (public mutable
 * properties, no encapsulation) — consistency with this codebase's already
 * established Entity pattern.
 */
class DataRoomGrant
{
    private function __construct(
        private readonly string $id,
        private readonly string $documentId,
        private readonly string $granteeUserId,
        private readonly PermissionTier $permissionTier,
        private readonly string $grantedBy,
        private readonly CarbonInterface $grantedAt,
        private ?string $revokedBy,
        private ?CarbonInterface $revokedAt,
        private ?CarbonInterface $expiresAt,
    ) {
    }

    public static function grant(
        string $id,
        string $documentId,
        string $granteeUserId,
        string $grantedBy,
        PermissionTier $permissionTier = PermissionTier::ViewOnly, // BR-041 default
        ?CarbonInterface $expiresAt = null,
    ): self {
        return new self($id, $documentId, $granteeUserId, $permissionTier, $grantedBy, now(), null, null, $expiresAt);
    }

    public static function reconstitute(
        string $id,
        string $documentId,
        string $granteeUserId,
        PermissionTier $permissionTier,
        string $grantedBy,
        CarbonInterface $grantedAt,
        ?string $revokedBy,
        ?CarbonInterface $revokedAt,
        ?CarbonInterface $expiresAt,
    ): self {
        return new self($id, $documentId, $granteeUserId, $permissionTier, $grantedBy, $grantedAt, $revokedBy, $revokedAt, $expiresAt);
    }

    /**
     * BR-045 ImmediateRevocationPolicy: sets revoked_at, never deletes the
     * row — the historical grant stays auditable. isActive() below is what
     * makes revocation "immediate" (checked at query time, never cached).
     */
    public function revoke(string $revokedBy): void
    {
        if ($this->revokedAt !== null) {
            throw new DomainException("DataRoomGrant {$this->id} is already revoked.");
        }

        $this->revokedBy = $revokedBy;
        $this->revokedAt = now();
    }

    public function isActive(): bool
    {
        if ($this->revokedAt !== null) {
            return false;
        }

        if ($this->expiresAt !== null && now()->gt($this->expiresAt)) {
            return false;
        }

        return true;
    }

    public function canDownload(): bool
    {
        return $this->isActive() && $this->permissionTier === PermissionTier::Downloadable;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function documentId(): string
    {
        return $this->documentId;
    }

    public function granteeUserId(): string
    {
        return $this->granteeUserId;
    }

    public function permissionTier(): PermissionTier
    {
        return $this->permissionTier;
    }

    public function grantedBy(): string
    {
        return $this->grantedBy;
    }

    public function grantedAt(): CarbonInterface
    {
        return $this->grantedAt;
    }

    public function revokedBy(): ?string
    {
        return $this->revokedBy;
    }

    public function revokedAt(): ?CarbonInterface
    {
        return $this->revokedAt;
    }

    public function expiresAt(): ?CarbonInterface
    {
        return $this->expiresAt;
    }
}
