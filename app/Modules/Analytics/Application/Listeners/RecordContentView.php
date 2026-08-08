<?php

namespace App\Modules\Analytics\Application\Listeners;

use App\Modules\Analytics\Domain\Events\DocumentViewed;
use App\Modules\Analytics\Domain\Events\ProjectViewed;
use App\Modules\Analytics\Domain\Repositories\ContentViewCountRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * A view counter is advisory reporting data, not a business decision — safe
 * to process off the request thread (PDL-024). A dropped/delayed increment
 * here must never block or fail the page-view request itself.
 */
class RecordContentView implements ShouldQueue
{
    public function __construct(private readonly ContentViewCountRepositoryInterface $viewCounts)
    {
    }

    public function handleProjectViewed(ProjectViewed $event): void
    {
        $this->viewCounts->recordView('project', $event->projectId);
    }

    public function handleDocumentViewed(DocumentViewed $event): void
    {
        $this->viewCounts->recordView('document', $event->documentId);
    }
}
