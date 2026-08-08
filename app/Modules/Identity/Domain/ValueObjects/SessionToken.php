<?php

namespace App\Modules\Identity\Domain\ValueObjects;

/**
 * SessionToken Value Object (06_DOMAIN_MODEL.md §2, Identity Context).
 *
 * Wraps the signed JWT issued to a User on successful authentication
 * (API-AUTH-002, `12_API_STANDARD.md`) plus its expiry, so the
 * Application layer never hands back a bare string without knowing when
 * it dies (SessionExpiryPolicy, BR-013).
 */
final class SessionToken
{
    private string $jwt;
    private int $expiresAt;

    private function __construct(string $jwt, int $expiresAt)
    {
        $this->jwt = $jwt;
        $this->expiresAt = $expiresAt;
    }

    public static function issue(string $jwt, int $expiresAtTimestamp): self
    {
        return new self($jwt, $expiresAtTimestamp);
    }

    public function toApiPayload(): array
    {
        return [
            'access_token' => $this->jwt,
            'token_type' => 'Bearer',
            'expires_at' => gmdate('c', $this->expiresAt),
        ];
    }
}
