<?php

namespace App\Modules\Investor\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** EVT-070 (07_EVENT_CATALOG.md) — Domain Event. */
class AccessRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $accessRequestId,
        public readonly string $projectId,
        public readonly string $investorUserId,
    ) {
    }
}
