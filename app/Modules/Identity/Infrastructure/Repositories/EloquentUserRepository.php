<?php

namespace App\Modules\Identity\Infrastructure\Repositories;

use App\Modules\Identity\Domain\Entities\User;
use App\Modules\Identity\Domain\Events\UserRoleAssigned;
use App\Modules\Identity\Domain\Repositories\UserRepositoryInterface;
use App\Modules\Identity\Infrastructure\Eloquent\UserModel;
use App\Modules\Identity\Infrastructure\Eloquent\UserRoleModel;
use App\Modules\Identity\Infrastructure\Mappers\UserMapper;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(private readonly UserMapper $mapper)
    {
    }

    public function find(string $id): ?User
    {
        $model = UserModel::find($id);

        return $model ? $this->mapper->toDomain($model, $this->activeRoles($model->id)) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $model = UserModel::where('email', strtolower($email))->first();

        return $model ? $this->mapper->toDomain($model, $this->activeRoles($model->id)) : null;
    }

    public function save(User $user): void
    {
        $existing = UserModel::find($user->id());
        $this->mapper->toModel($user, $existing)->save();
        $this->syncRoles($user->id(), $user->roles()->toArray());
    }

    public function existsByEmail(string $email): bool
    {
        return UserModel::where('email', strtolower($email))->exists();
    }

    /**
     * @return string[]
     */
    private function activeRoles(string $userId): array
    {
        return UserRoleModel::where('user_id', $userId)
            ->whereNull('revoked_at')
            ->pluck('role')
            ->all();
    }

    /**
     * DB-002's own discipline: a role is never deleted, only granted
     * (new row) or revoked (`revoked_at` set on the existing row) — role
     * history is preserved for UserRoleAssigned/EVT-002 auditing.
     *
     * @param string[] $desiredRoles
     */
    private function syncRoles(string $userId, array $desiredRoles): void
    {
        $current = $this->activeRoles($userId);
        $added = array_diff($desiredRoles, $current);
        $removed = array_diff($current, $desiredRoles);

        if (empty($added) && empty($removed)) {
            return;
        }

        foreach ($added as $newRole) {
            UserRoleModel::create([
                'user_id' => $userId,
                'role' => $newRole,
                'granted_at' => now(),
            ]);
        }

        foreach ($removed as $removedRole) {
            UserRoleModel::where('user_id', $userId)
                ->where('role', $removedRole)
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);
        }

        // EVT-002's locked payload is the full before/after snapshot, not a
        // per-role delta — dispatched once for the whole sync, not once per role.
        UserRoleAssigned::dispatch($userId, array_values($current), array_values($desiredRoles));
    }
}
