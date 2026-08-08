<?php

namespace App\Modules\Identity\Domain\Repositories;

use App\Modules\Identity\Domain\Entities\User;

interface UserRepositoryInterface
{
    public function find(string $id): ?User;

    public function findByEmail(string $email): ?User;

    public function save(User $user): void;

    public function existsByEmail(string $email): bool;
}
