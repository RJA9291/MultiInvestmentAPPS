<?php

namespace App\Modules\Identity\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * PasswordHash Value Object (06_DOMAIN_MODEL.md §2, Identity Context).
 *
 * Enforces BR-004 ("A user's password must be stored using a one-way hash;
 * plaintext passwords must never be stored or logged") structurally: the
 * only way to construct this VO from a plaintext string is via
 * `fromPlainText()`, which hashes immediately and never retains the
 * plaintext value anywhere in the object.
 *
 * PasswordPolicy (BR-004) minimum-strength check: at least 8 characters.
 * This is a deliberately conservative Sprint 12 default — a fuller
 * complexity policy (uppercase/number/symbol requirements) is not yet
 * specified in `04_BUSINESS_RULES.md` and is flagged as an open item
 * rather than invented here.
 */
final class PasswordHash
{
    private const MIN_LENGTH = 8;

    private string $hash;

    private function __construct(string $hash)
    {
        $this->hash = $hash;
    }

    public static function fromPlainText(string $plainText): self
    {
        if (mb_strlen($plainText) < self::MIN_LENGTH) {
            throw new InvalidArgumentException(
                'Password must be at least ' . self::MIN_LENGTH . ' characters (PasswordPolicy, BR-004).'
            );
        }

        return new self(password_hash($plainText, PASSWORD_BCRYPT));
    }

    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }

    public function verify(string $plainText): bool
    {
        return password_verify($plainText, $this->hash);
    }

    public function toString(): string
    {
        return $this->hash;
    }
}
