<?php

namespace App\Modules\Identity\Domain\Entities;

use App\Modules\Identity\Domain\ValueObjects\EmailAddress;
use App\Modules\Identity\Domain\ValueObjects\PasswordHash;
use App\Modules\Identity\Domain\ValueObjects\RoleSet;
use DomainException;

/**
 * User Aggregate Root (06_DOMAIN_MODEL.md §2, Identity Context;
 * `08_DATABASE_DESIGN.md` DB-001 `users` / DB-002 `user_roles` / DB-003
 * `sessions`).
 *
 * This is the Aggregate every other Module's `*_user_id` / `UserReference`
 * field has been pointing at since Sprint 12 began (see e.g. Investor
 * Module's `InvestorProfile::$investorUserId` docblock) — it did not exist
 * as real code until this build.
 *
 * `roles` (RoleSet) is persisted across a separate `user_roles` pivot table
 * (DB-002), not a column on this Aggregate's own row — EloquentUserRepository
 * is responsible for diffing the RoleSet against active grants on save().
 * Session revocation (SEC-010: "invalidated immediately on password/
 * credential change") is likewise NOT a field on this Entity — it is
 * enforced by ending the User's `sessions` (DB-003) rows, orchestrated by
 * AuthenticationService, not by this Aggregate holding a version counter.
 *
 * `displayName` is not one of the four locked Value Objects (EmailAddress,
 * PasswordHash, RoleSet, SessionToken) but is required to satisfy the
 * already-locked Shared Kernel `UserReference` VO (06_DOMAIN_MODEL.md §13:
 * "Immutable, minimal pointer to a User (ID + display name)") — added to
 * close that existing structural dependency, not invented independently.
 */
class User
{
    private function __construct(
        private readonly string $id,
        private string $displayName,
        private EmailAddress $email,
        private PasswordHash $passwordHash,
        private RoleSet $roles,
        private bool $mfaEnabled,
        private bool $isSuspended,
    ) {
    }

    public static function register(
        string $id,
        string $displayName,
        EmailAddress $email,
        PasswordHash $passwordHash,
        RoleSet $roles,
    ): self {
        return new self($id, $displayName, $email, $passwordHash, $roles, false, false);
    }

    public static function reconstitute(
        string $id,
        string $displayName,
        EmailAddress $email,
        PasswordHash $passwordHash,
        RoleSet $roles,
        bool $mfaEnabled,
        bool $isSuspended,
    ): self {
        return new self($id, $displayName, $email, $passwordHash, $roles, $mfaEnabled, $isSuspended);
    }

    /**
     * Caller (a future password-change endpoint, not built this Sprint —
     * flagged, not fabricated) is responsible for dispatching PasswordChanged
     * (EVT-006) and ending all of this user's active `sessions` rows
     * (DB-003) in the same transaction, per SEC-010.
     */
    public function changePassword(PasswordHash $newHash): void
    {
        $this->passwordHash = $newHash;
    }

    public function verifyPassword(string $plainText): bool
    {
        if ($this->isSuspended) {
            return false;
        }

        return $this->passwordHash->verify($plainText);
    }

    /**
     * AdminAccountUsagePolicy (BR-008) is enforced at the call site (an
     * Admin-role account must never be the acting user on ordinary Business
     * Owner/Investor endpoints) — flagged as a Sprint 12 open item since no
     * such endpoint-level check exists yet across the other 8 Modules.
     *
     * Dispatches UserSuspended (EVT-003) — caller's responsibility, same
     * pattern as changePassword().
     */
    public function suspend(): void
    {
        if ($this->isSuspended) {
            throw new DomainException("User {$this->id} is already suspended.");
        }

        $this->isSuspended = true;
    }

    public function isSuspended(): bool
    {
        return $this->isSuspended;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function displayName(): string
    {
        return $this->displayName;
    }

    public function email(): EmailAddress
    {
        return $this->email;
    }

    public function passwordHash(): PasswordHash
    {
        return $this->passwordHash;
    }

    public function roles(): RoleSet
    {
        return $this->roles;
    }

    public function mfaEnabled(): bool
    {
        return $this->mfaEnabled;
    }
}
