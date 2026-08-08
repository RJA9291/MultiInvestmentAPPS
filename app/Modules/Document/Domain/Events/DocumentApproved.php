<?php

namespace App\Modules\Document\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * EVT-062 (07_EVENT_CATALOG.md) — Business Event, informs the SAME context's
 * PublishEligibilityPolicy (BR-017), also consumed by Administration.
 * Fires when a document "passes malware/type/completeness checks" — an
 * automated ingest gate, not a human sign-off decision.
 */
class DocumentApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $documentId,
        public readonly string $approvedBy,
    ) {
    }
}
