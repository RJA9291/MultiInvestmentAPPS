<?php

namespace App\Modules\DataRoom\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** EVT-023 (07_EVENT_CATALOG.md) — Business Event, consumed by Administration. */
class NdaAcknowledged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $grantId,
        public readonly string $ndaVersionHash,
        public readonly string $acknowledgedAt,
    ) {
    }
}
