<?php

namespace App\Modules\Identity\Infrastructure\Services;

use App\Modules\Identity\Infrastructure\Eloquent\SessionModel;
use Illuminate\Support\Str;

/**
 * Infrastructure-level access to `sessions` (DB-003) — not a Domain
 * Repository, since 06_DOMAIN_MODEL.md §2 lists only one Repository
 * (`UserRepository`) for the whole Identity Context; `Session` is an
 * Entity of the User Aggregate, and its simple create/end lifecycle is
 * treated the same way `JwtTokenCodec` treats JWT encoding — an
 * Infrastructure concern the Application layer (AuthenticationService)
 * orchestrates, not a second Aggregate root.
 *
 * This is what makes DB-003's own stated purpose true: "session validity
 * is checked on every request, not cached, to avoid serving a revoked
 * session" — the JWT's signature/exp alone is not enough, because a JWT
 * cannot be revoked before its natural expiry without a server-side
 * record. `token_hash` is `sha256(jwt)`, so a stolen/forged token that
 * merely reuses a valid `sid` still fails this check.
 */
class SessionStore
{
    public function start(string $userId, ?string $ipAddress = null): SessionModel
    {
        return SessionModel::create([
            'id' => (string) Str::uuid(),
            'user_id' => $userId,
            'token_hash' => null, // filled in by finalize() once the JWT (which embeds this session's id) exists
            'expires_at' => now(), // placeholder, corrected by finalize()
            'ip_address' => $ipAddress,
        ]);
    }

    public function finalize(SessionModel $session, string $jwt, int $expiresAtTimestamp): void
    {
        $session->update([
            'token_hash' => hash('sha256', $jwt),
            'expires_at' => date('Y-m-d H:i:s', $expiresAtTimestamp),
        ]);
    }

    public function findActive(string $sessionId, string $presentedJwt): ?SessionModel
    {
        $session = SessionModel::find($sessionId);

        if (! $session || $session->ended_at !== null || $session->token_hash === null) {
            return null;
        }

        if ($session->expires_at->isPast()) {
            return null;
        }

        if (! hash_equals($session->token_hash, hash('sha256', $presentedJwt))) {
            return null;
        }

        return $session;
    }

    public function end(string $sessionId): void
    {
        SessionModel::where('id', $sessionId)->whereNull('ended_at')->update(['ended_at' => now()]);
    }

    /**
     * Not called anywhere this Sprint (no password-change endpoint yet) —
     * exists so SEC-010's "invalidated immediately on password/credential
     * change" has a ready implementation the moment that endpoint is built.
     */
    public function endAllForUser(string $userId): void
    {
        SessionModel::where('user_id', $userId)->whereNull('ended_at')->update(['ended_at' => now()]);
    }
}
