<?php

namespace App\Modules\DataRoom\Domain\Events;

use App\Modules\DataRoom\Domain\ValueObjects\PermissionTier;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** EVT-021 (07_EVENT_CATALOG.md) — Business Event, consumed by Notification/Administration. */
class DataRoomPermissionGranted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $grantId,
        public readonly string $documentId,
        public readonly string $granteeUserId,
        public readonly PermissionTier $permissionTier,
    ) {
    }
}
