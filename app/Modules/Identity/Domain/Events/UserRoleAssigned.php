<?php

namespace App\Modules\Identity\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * EVT-002 (07_EVENT_CATALOG.md — locked payload: UserId, PreviousRoles,
 * NewRoles). Dispatched once per EloquentUserRepository::syncRoles() call
 * with the full before/after RoleSet snapshot — NOT once per individual
 * role added, so a consumer always sees the complete transition rather
 * than having to reassemble it from a stream of single-role events.
 */
class UserRoleAssigned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $userId,
        public readonly array $previousRoles,
        public readonly array $newRoles,
    ) {
    }
}
