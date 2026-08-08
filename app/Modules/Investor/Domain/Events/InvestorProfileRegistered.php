<?php

namespace App\Modules\Investor\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** EVT-067 (07_EVENT_CATALOG.md) — Domain Event. */
class InvestorProfileRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $investorProfileId,
        public readonly string $investorUserId,
    ) {
    }
}
