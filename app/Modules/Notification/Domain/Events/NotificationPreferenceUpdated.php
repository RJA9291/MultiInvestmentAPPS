<?php

namespace App\Modules\Notification\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** EVT-055 (07_EVENT_CATALOG.md) — Domain Event. Payload: UserId, ChangedChannel. */
class NotificationPreferenceUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $userId,
        public readonly string $changedChannel,
    ) {
    }
}
