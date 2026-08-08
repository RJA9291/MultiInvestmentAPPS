<?php

namespace App\Modules\Investor\Domain\ValueObjects;

enum InvestorVerificationStatus: string
{
    case Pending = 'PENDING';
    case Verified = 'VERIFIED';
    case Rejected = 'REJECTED';
}
