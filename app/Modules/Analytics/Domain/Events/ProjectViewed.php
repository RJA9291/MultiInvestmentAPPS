<?php

namespace App\Modules\Analytics\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * EVT-073 (07_EVENT_CATALOG.md) — Domain Event, Investment Context.
 *
 * Fired from `GetProjectController` (any authenticated viewer — Business
 * Owner, Compliance Officer, Admin — viewing a Project's own record).
 * Deliberately distinct from the already-locked EVT-024
 * `DataRoomDocumentViewed`, which is specifically the DataRoom-gated event
 * of an approved Investor viewing a granted Document's content. This event
 * is a plain "the Project record was looked at" signal, consumed only by
 * the Analytics reporting layer to maintain `content_view_counts` (DB-048)
 * — it carries no DataRoomGrant/permission meaning and must never be used
 * as an access-control signal.
 */
class ProjectViewed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $projectId,
        public readonly ?string $viewedByUserId,
    ) {
    }
}
