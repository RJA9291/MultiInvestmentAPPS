<?php

namespace App\Modules\Investor\Domain\ValueObjects;

enum AccessRequestStatus: string
{
    case Pending = 'PENDING';
    case Approved = 'APPROVED';
    case Rejected = 'REJECTED';
}
