<?php

namespace App\Modules\Identity\Interfaces\Http\Resources;

use App\Modules\Identity\Domain\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $this->resource;

        return [
            'id' => $user->id(),
            'display_name' => $user->displayName(),
            'email' => $user->email()->toString(),
            'roles' => $user->roles()->toArray(),
            'mfa_enabled' => $user->mfaEnabled(),
        ];
    }
}
