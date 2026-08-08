<?php

namespace App\Modules\Identity\Infrastructure\Mappers;

use App\Modules\Identity\Domain\Entities\User;
use App\Modules\Identity\Domain\ValueObjects\EmailAddress;
use App\Modules\Identity\Domain\ValueObjects\PasswordHash;
use App\Modules\Identity\Domain\ValueObjects\RoleSet;
use App\Modules\Identity\Infrastructure\Eloquent\UserModel;

class UserMapper
{
    /**
     * @param string[] $activeRoles roles currently granted (revoked_at IS
     * NULL) for this user, queried by the Repository from `user_roles`
     * (DB-002) — this Mapper never touches that table itself.
     */
    public function toDomain(UserModel $model, array $activeRoles): User
    {
        return User::reconstitute(
            id: $model->id,
            displayName: $model->display_name,
            email: EmailAddress::fromString($model->email),
            passwordHash: PasswordHash::fromHash($model->password_hash),
            roles: RoleSet::fromArray($activeRoles),
            mfaEnabled: $model->mfa_enabled,
            isSuspended: $model->is_suspended,
        );
    }

    /**
     * Only maps `users` (DB-001) columns. Role grants/revocations against
     * `user_roles` (DB-002) are handled separately by
     * EloquentUserRepository::syncRoles() — deliberately not this class's
     * concern, since one is a straight column mapping and the other is a
     * diff-and-audit operation.
     *
     * `id` is deliberately NOT in UserModel::$fillable, same mass-
     * assignment-avoidance pattern used by every other Module's Mapper
     * this Sprint (e.g. InvestorProfileMapper).
     */
    public function toModel(User $user, ?UserModel $existing = null): UserModel
    {
        $model = $existing ?? new UserModel();

        if (! $existing) {
            $model->id = $user->id();
        }

        $model->fill([
            'display_name' => $user->displayName(),
            'email' => $user->email()->toString(),
            'password_hash' => $user->passwordHash()->toString(),
            'mfa_enabled' => $user->mfaEnabled(),
            'is_suspended' => $user->isSuspended(),
        ]);

        return $model;
    }
}
