<?php

namespace App\Modules\Identity\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Identity\Application\Services\AuthenticationService;
use App\Modules\Identity\Interfaces\Http\Requests\LoginRequest;
use DomainException;

/** API-034: POST /v1/auth/login */
class LoginController
{
    public function __invoke(LoginRequest $request, AuthenticationService $auth)
    {
        $validated = $request->validated();

        try {
            $token = $auth->attempt($validated['email'], $validated['password'], $request->ip());
        } catch (DomainException $e) {
            return ApiResponse::error('INVALID_CREDENTIALS', $e->getMessage(), status: 401);
        }

        return ApiResponse::success($token->toApiPayload());
    }
}
