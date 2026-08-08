<?php

namespace App\Modules\Investor\Domain\ValueObjects;

enum InvestorType: string
{
    case Individual = 'INDIVIDUAL';
    case Company = 'COMPANY';
}
