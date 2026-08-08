<?php

namespace App\Modules\Notification\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** EVT-054 (07_EVENT_CATALOG.md) — Notification Event, consumed by Administration. Payload: NotificationId, ReadAt. */
class NotificationRead
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $notificationId,
        public readonly string $readAt,
    ) {
    }
}
