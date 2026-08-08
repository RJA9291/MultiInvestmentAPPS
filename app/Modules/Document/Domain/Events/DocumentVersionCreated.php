<?php

namespace App\Modules\Document\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** EVT-018 (07_EVENT_CATALOG.md) — Domain Event, consumed by Knowledge/Notification. */
class DocumentVersionCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $documentId,
        public readonly int $versionNumber,
    ) {
    }
}
