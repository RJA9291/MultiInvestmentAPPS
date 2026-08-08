<?php

namespace App\Modules\Identity\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Identity\Interfaces\Http\Resources\UserResource;
use Illuminate\Http\Request;

/** API-036: GET /v1/auth/me */
class MeController
{
    public function __invoke(Request $request)
    {
        return ApiResponse::success(new UserResource($request->attributes->get('auth_user')));
    }
}
