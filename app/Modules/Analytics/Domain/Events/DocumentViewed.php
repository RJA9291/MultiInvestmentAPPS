<?php

namespace App\Modules\Analytics\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * EVT-074 (07_EVENT_CATALOG.md) — Domain Event, Investment Context.
 *
 * Fired from `GetDocumentController` (any authenticated viewer looking at a
 * Document's own metadata record — e.g. a Business Owner or Compliance
 * Officer, not necessarily a DataRoom-granted Investor). Deliberately kept
 * separate from EVT-024 `DataRoomDocumentViewed` (Investor viewing granted
 * content through the Data Room) — the two events model different business
 * moments and must not be merged, per the same reasoning as `ProjectViewed`.
 */
class DocumentViewed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $documentId,
        public readonly ?string $viewedByUserId,
    ) {
    }
}
