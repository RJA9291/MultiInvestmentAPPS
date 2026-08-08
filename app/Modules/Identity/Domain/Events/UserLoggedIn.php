<?php

namespace App\Modules\Identity\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** EVT-007 (07_EVENT_CATALOG.md — locked payload: UserId, SessionId, IpAddress). */
class UserLoggedIn
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $userId,
        public readonly string $sessionId,
        public readonly ?string $ipAddress = null,
    ) {
    }
}
