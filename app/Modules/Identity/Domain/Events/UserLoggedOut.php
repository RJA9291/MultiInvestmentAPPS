<?php

namespace App\Modules\Identity\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** EVT-008 (07_EVENT_CATALOG.md — locked payload: UserId, SessionId). */
class UserLoggedOut
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $userId,
        public readonly string $sessionId,
    ) {
    }
}
