<?php

namespace App\Modules\DataRoom\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** EVT-024 (07_EVENT_CATALOG.md) — Domain Event, consumed by Administration (BR-047 verification). */
class DataRoomDocumentViewed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $grantId,
        public readonly string $documentId,
        public readonly string $viewedAt,
    ) {
    }
}
