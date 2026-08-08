<?php

namespace App\Modules\Identity\Interfaces\Http\Middleware;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Identity\Domain\Repositories\UserRepositoryInterface;
use App\Modules\Identity\Infrastructure\Services\JwtTokenCodec;
use App\Modules\Identity\Infrastructure\Services\SessionStore;
use Closure;
use Illuminate\Http\Request;

/**
 * Enforces BR-003 ("Only an authenticated user may access any project,
 * document, Workspace, or AI capability") platform-wide. Applied to the
 * entire `/v1` route group in routes/api.php except `/v1/auth/login`
 * (`12_API_STANDARD.md` conventions — a request needs no prior token to
 * obtain one).
 *
 * Checks the `sessions` row (DB-003) the JWT's `sid` claim points at, not
 * just the JWT's own signature/exp — this is what makes SEC-010's
 * "invalidated immediately on password/credential change" and "session
 * validity checked on every request, not cached" both true for an
 * otherwise self-contained token (see AuthenticationService docblock).
 */
class JwtAuthenticate
{
    public function __construct(
        private readonly JwtTokenCodec $codec,
        private readonly UserRepositoryInterface $users,
        private readonly SessionStore $sessions,
    ) {
    }

    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('Authorization', '');

        if (! str_starts_with($header, 'Bearer ')) {
            return ApiResponse::error('UNAUTHENTICATED', 'Missing or malformed Authorization header.', status: 401);
        }

        $jwt = substr($header, 7);
        $claims = $this->codec->decode($jwt);

        if (! $claims || empty($claims['sub']) || empty($claims['sid'])) {
            return ApiResponse::error('UNAUTHENTICATED', 'Invalid or expired token.', status: 401);
        }

        $session = $this->sessions->findActive($claims['sid'], $jwt);

        if (! $session) {
            return ApiResponse::error('UNAUTHENTICATED', 'Session no longer valid.', status: 401);
        }

        $user = $this->users->find($claims['sub']);

        if (! $user || $user->isSuspended()) {
            return ApiResponse::error('UNAUTHENTICATED', 'Account no longer active.', status: 401);
        }

        $request->attributes->set('auth_user', $user);
        $request->attributes->set('auth_session_id', $session->id);

        return $next($request);
    }
}
