<?php

namespace App\Modules\Identity\Application\Services;

use App\Modules\Identity\Domain\Events\UserLoggedIn;
use App\Modules\Identity\Domain\Events\UserLoggedOut;
use App\Modules\Identity\Domain\Repositories\UserRepositoryInterface;
use App\Modules\Identity\Domain\ValueObjects\SessionToken;
use App\Modules\Identity\Infrastructure\Services\JwtTokenCodec;
use App\Modules\Identity\Infrastructure\Services\SessionStore;
use DomainException;

/**
 * AuthenticationService (06_DOMAIN_MODEL.md §2, Identity Context).
 *
 * Owns credential verification and JWT issuance. Every JWT embeds a `sid`
 * claim pointing at a `sessions` row (DB-003) — this is how SEC-010's
 * "invalidated immediately on password/credential change" and "not cached,
 * to avoid serving a revoked session" are both satisfied for an otherwise
 * self-contained token: JwtAuthenticate re-checks the session row on every
 * request, not just the JWT's own signature/exp.
 */
class AuthenticationService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly JwtTokenCodec $codec,
        private readonly SessionStore $sessions,
    ) {
    }

    public function attempt(string $email, string $plainPassword, ?string $ipAddress = null): SessionToken
    {
        $user = $this->users->findByEmail($email);

        if (! $user || ! $user->verifyPassword($plainPassword)) {
            throw new DomainException('Invalid credentials.');
        }

        $session = $this->sessions->start($user->id(), $ipAddress);

        $issued = $this->codec->encode([
            'sub' => $user->id(),
            'sid' => $session->id,
        ]);

        $this->sessions->finalize($session, $issued['jwt'], $issued['expires_at']);

        UserLoggedIn::dispatch($user->id(), $session->id, $ipAddress);

        return SessionToken::issue($issued['jwt'], $issued['expires_at']);
    }

    public function logout(string $userId, string $sessionId): void
    {
        $this->sessions->end($sessionId);

        UserLoggedOut::dispatch($userId, $sessionId);
    }
}
