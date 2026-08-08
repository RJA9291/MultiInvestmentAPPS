<?php

namespace App\Modules\Investor\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** EVT-071 (07_EVENT_CATALOG.md) — Domain Event. */
class AccessRequestApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $accessRequestId,
        public readonly string $projectId,
        public readonly string $investorUserId,
        public readonly string $decidedBy,
    ) {
    }
}
