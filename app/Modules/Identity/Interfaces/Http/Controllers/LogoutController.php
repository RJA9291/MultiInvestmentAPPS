<?php

namespace App\Modules\Identity\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Identity\Application\Services\AuthenticationService;
use App\Modules\Identity\Domain\Entities\User;
use Illuminate\Http\Request;

/** API-035: POST /v1/auth/logout */
class LogoutController
{
    public function __invoke(Request $request, AuthenticationService $auth)
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $sessionId = $request->attributes->get('auth_session_id');

        $auth->logout($user->id(), $sessionId);

        return ApiResponse::success(['message' => 'Logged out.']);
    }
}
