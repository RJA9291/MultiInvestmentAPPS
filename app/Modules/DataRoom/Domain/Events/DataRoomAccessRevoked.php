<?php

namespace App\Modules\DataRoom\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** EVT-022 (07_EVENT_CATALOG.md) — Business Event, consumed by Notification/Administration. */
class DataRoomAccessRevoked
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $grantId,
        public readonly string $revokedBy,
    ) {
    }
}
