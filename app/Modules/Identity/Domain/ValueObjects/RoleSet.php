<?php

namespace App\Modules\Identity\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * RoleSet Value Object (06_DOMAIN_MODEL.md §2, Identity Context).
 *
 * Combinable per BR-002 ("A user may hold both Business Owner and Investor
 * roles on the same account") — deliberately a set, not a single enum value.
 *
 * Allowed roles are exactly the four named in the locked Identity Context:
 * Business Owner / Investor / Admin / Compliance Officer (Compliance Officer
 * added v4.0.0, BR-139).
 */
final class RoleSet
{
    public const BUSINESS_OWNER = 'business_owner';
    public const INVESTOR = 'investor';
    public const ADMIN = 'admin';
    public const COMPLIANCE_OFFICER = 'compliance_officer';

    private const ALLOWED = [
        self::BUSINESS_OWNER,
        self::INVESTOR,
        self::ADMIN,
        self::COMPLIANCE_OFFICER,
    ];

    /** @var string[] */
    private array $roles;

    private function __construct(array $roles)
    {
        $this->roles = $roles;
    }

    /**
     * @param string[] $roles
     */
    public static function fromArray(array $roles): self
    {
        if (empty($roles)) {
            throw new InvalidArgumentException('RoleSet must contain at least one role.');
        }

        foreach ($roles as $role) {
            if (! in_array($role, self::ALLOWED, true)) {
                throw new InvalidArgumentException("Unknown role: {$role}");
            }
        }

        return new self(array_values(array_unique($roles)));
    }

    public function has(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->has(self::ADMIN);
    }

    /**
     * @return string[]
     */
    public function toArray(): array
    {
        return $this->roles;
    }
}
