<?php

namespace App\Modules\Identity\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** EVT-001 (07_EVENT_CATALOG.md; already reserved in 08_DATABASE_DESIGN.md DB-001) — Domain Event. */
class UserRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $userId,
        public readonly string $email,
        public readonly array $roles,
    ) {
    }
}
