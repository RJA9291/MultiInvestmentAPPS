<?php

namespace App\Modules\Notification\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** EVT-053 (07_EVENT_CATALOG.md) — Notification Event, consumed by Administration. Payload: NotificationId, Channel, DeliveredAt. */
class NotificationDelivered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $notificationId,
        public readonly string $channel,
        public readonly string $deliveredAt,
    ) {
    }
}
