<?php

namespace App\Modules\Document\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * EVT-020 (07_EVENT_CATALOG.md) — Infrastructure Event, consumed by
 * Notification/Administration (BR-033). This is the locked negative
 * counterpart to DocumentApproved — there is no separate "DocumentRejected"
 * event in the approved catalog. The Project Owner's Document-brief
 * "reject" endpoint maps to THIS event, not a new one.
 *
 * FLAGGED gap (08_DATABASE_DESIGN.md DB-006's own Future Expansion note):
 * a scan failure is NOT persisted anywhere on the `documents` row today —
 * only this event fires. A failed document simply never becomes
 * `is_approved = true`; distinguishing "failed" from "not yet checked" at
 * the database level is tracked future work, not invented here.
 */
class DocumentScanFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $documentId,
        public readonly string $scanEngine,
        public readonly string $errorCode,
    ) {
    }
}
