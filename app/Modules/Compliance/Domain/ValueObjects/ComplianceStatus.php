<?php

namespace App\Modules\Compliance\Domain\ValueObjects;

/** ComplianceStatus (06_DOMAIN_MODEL.md §10) */
enum ComplianceStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
