<?php

namespace App\Modules\Identity\Infrastructure\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Thin wrapper over firebase/php-jwt (API-AUTH-002, `12_API_STANDARD.md`:
 * "JWT as the token format for authenticated sessions, short-lived per
 * SEC-010, carried in the Authorization: Bearer header").
 *
 * Package choice (`firebase/php-jwt` over `tymon/jwt-auth`) is a Sprint 12
 * implementation decision explicitly left open by `14_LARAVEL_BLUEPRINT.md`
 * §477 ("which JWT package ... are Sprint 12 implementation decisions").
 * Chosen because this Module hand-rolls its own stateless auth (no Sanctum
 * cookie/session machinery needed for a pure JSON API), and firebase/php-jwt
 * has no framework coupling of its own.
 *
 * Session TTL default: 60 minutes. Not specified anywhere in the locked
 * docs (`14_LARAVEL_BLUEPRINT.md` §498 flags the exact TTL as an open,
 * data-driven decision for later) — this is a documented Sprint 12 default,
 * overridable via `config/security.php`'s `session_ttl_minutes`, not a
 * silently invented constant.
 */
class JwtTokenCodec
{
    public function __construct(
        private readonly string $secret,
        private readonly int $ttlMinutes = 60,
    ) {
    }

    /**
     * @param array<string, mixed> $claims
     * @return array{jwt: string, expires_at: int}
     */
    public function encode(array $claims): array
    {
        $now = time();
        $expiresAt = $now + ($this->ttlMinutes * 60);

        $payload = array_merge($claims, [
            'iat' => $now,
            'exp' => $expiresAt,
        ]);

        $jwt = JWT::encode($payload, $this->secret, 'HS256');

        return ['jwt' => $jwt, 'expires_at' => $expiresAt];
    }

    /**
     * @return array<string, mixed>|null null if invalid/expired/malformed —
     * caller (JwtAuthenticate middleware) treats null as 401, never throws
     * a raw JWT library exception up to an HTTP response.
     */
    public function decode(string $jwt): ?array
    {
        try {
            $decoded = JWT::decode($jwt, new Key($this->secret, 'HS256'));

            return (array) $decoded;
        } catch (\Throwable) {
            return null;
        }
    }
}
