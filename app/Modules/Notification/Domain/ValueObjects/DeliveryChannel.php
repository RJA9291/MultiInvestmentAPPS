<?php

namespace App\Modules\Notification\Domain\ValueObjects;

/** DeliveryChannel (06_DOMAIN_MODEL.md §8) — in-app / email today; push flagged as Future Expansion (DB-028), not built. */
enum DeliveryChannel: string
{
    case InApp = 'in_app';
    case Email = 'email';
}
