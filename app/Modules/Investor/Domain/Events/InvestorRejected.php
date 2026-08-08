<?php

namespace App\Modules\Investor\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** EVT-069 (07_EVENT_CATALOG.md) — Domain Event. */
class InvestorRejected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $investorProfileId,
        public readonly string $rejectedBy,
    ) {
    }
}
