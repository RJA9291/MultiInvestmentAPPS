<?php

namespace App\Modules\Document\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** EVT-019 (07_EVENT_CATALOG.md) — Domain Event, consumed by Knowledge/Administration. Soft delete only (BR-032). */
class DocumentDeleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $documentId,
        public readonly string $deletedBy,
    ) {
    }
}
