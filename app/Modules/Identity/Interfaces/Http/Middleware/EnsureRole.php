<?php

namespace App\Modules\Identity\Interfaces\Http\Middleware;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Identity\Application\Services\AuthorizationGate;
use App\Modules\Identity\Domain\Entities\User;
use Closure;
use Illuminate\Http\Request;

/**
 * Route-level role gate, e.g. `Route::middleware('role:compliance_officer')`.
 * Runs after JwtAuthenticate (must find `auth_user` already on the request).
 */
class EnsureRole
{
    public function __construct(private readonly AuthorizationGate $gate)
    {
    }

    public function handle(Request $request, Closure $next, string ...$roles)
    {
        /** @var User|null $user */
        $user = $request->attributes->get('auth_user');

        if (! $user || ! $this->gate->allows($user->roles()->toArray(), $roles)) {
            return ApiResponse::error('FORBIDDEN', 'You do not have permission to perform this action.', status: 403);
        }

        return $next($request);
    }
}
