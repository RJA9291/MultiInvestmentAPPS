<?php

namespace App\Modules\Project\Domain\Policies;

use App\Modules\Project\Domain\ValueObjects\PublishState;

/**
 * ComplianceReviewGatePolicy (BR-139, BR-140, PDL-026)
 *
 * A Project may not transition Submitted/UnderComplianceReview -> Published
 * directly. A rejected Project can never become Published without a fresh
 * Approved decision (a new ComplianceReview cycle, PDL-027).
 */
class ComplianceReviewGatePolicy
{
    public function canPublish(PublishState $current): bool
    {
        return $current === PublishState::Approved;
    }
}
