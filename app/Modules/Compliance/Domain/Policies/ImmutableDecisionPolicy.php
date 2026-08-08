<?php

namespace App\Modules\Compliance\Domain\Policies;

use App\Modules\Compliance\Domain\ValueObjects\ComplianceStatus;

/**
 * ImmutableDecisionPolicy (BR-140, PDL-027)
 * Once a ReviewDecision is recorded (status leaves Pending), no code path
 * may update it — a correction is always a new review cycle.
 */
class ImmutableDecisionPolicy
{
    public function canModify(ComplianceStatus $currentStatus): bool
    {
        return $currentStatus === ComplianceStatus::Pending;
    }
}
