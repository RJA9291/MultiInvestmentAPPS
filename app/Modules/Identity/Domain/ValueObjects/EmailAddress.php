<?php

namespace App\Modules\Identity\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * EmailAddress Value Object (06_DOMAIN_MODEL.md §2, Identity Context).
 */
final class EmailAddress
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        $normalized = strtolower(trim($value));

        if (! filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address: {$value}");
        }

        return new self($normalized);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
