<?php

namespace App\Modules\Compliance\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * ProjectComplianceApproved (07_EVENT_CATALOG.md, Compliance Context)
 * Consumed by Project Module's listeners (Application/Listeners/OnProjectComplianceApproved.php)
 * — the documented Anti-Corruption Layer boundary between the two contexts.
 */
class ProjectComplianceApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $complianceReviewId,
        public readonly string $projectId,
    ) {
    }
}
