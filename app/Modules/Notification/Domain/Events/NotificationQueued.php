<?php

namespace App\Modules\Notification\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** EVT-052 (07_EVENT_CATALOG.md) — Notification Event. Payload: NotificationId, RecipientUserId, NotificationType. */
class NotificationQueued
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $notificationId,
        public readonly string $recipientUserId,
        public readonly string $notificationType,
    ) {
    }
}
