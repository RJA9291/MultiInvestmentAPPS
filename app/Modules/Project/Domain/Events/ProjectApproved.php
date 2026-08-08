<?php

namespace App\Modules\Project\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * ProjectApproved (07_EVENT_CATALOG.md, Investment Context - Project Aggregate)
 * Dispatched by Application/Services/ProjectPublishingService.
 */
class ProjectApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $projectId,
        public readonly array $payload = [],
    ) {
    }
}
