<?php

namespace App\Modules\Identity\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * EVT-006 (07_EVENT_CATALOG.md; already reserved in 08_DATABASE_DESIGN.md
 * DB-001) — Domain Event.
 *
 * Not dispatched by `accounts:create` (initial password set is part of
 * UserRegistered, EVT-001, not a "change") — reserved for a future
 * password-change/reset endpoint, not built this Sprint (flagged, not
 * fabricated). When built, it must end all of that user's active
 * `sessions` (DB-003) rows in the same transaction, per SEC-010.
 */
class PasswordChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $userId,
    ) {
    }
}
