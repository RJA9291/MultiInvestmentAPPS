<?php

namespace App\Modules\Document\Application\Services;

use App\Modules\Document\Domain\Entities\Document;
use App\Modules\Document\Domain\Events\DocumentApproved;
use App\Modules\Document\Domain\Events\DocumentScanFailed;
use App\Modules\Document\Domain\Repositories\DocumentRepositoryInterface;
use RuntimeException;

/**
 * DocumentApprovalService — proposed API-019 (approve) / API-020 (reject).
 *
 * "Approve" (DocumentApproved, EVT-062) marks that the document passes
 * malware/type/completeness checks and becomes eligible toward
 * PublishEligibilityPolicy (BR-017) — see Document::markApproved()'s doc
 * comment. This is deliberately NOT the same concept as a human Compliance
 * Officer decision (that is ComplianceDecisionService, at the Project
 * level, already built).
 *
 * "Reject" maps to DocumentScanFailed (EVT-020) — the locked catalog's
 * actual negative counterpart — NOT a new "DocumentRejected" event the
 * Project Owner's brief proposed but which does not exist in
 * `07_EVENT_CATALOG.md`. Per that event's own flagged gap: this does NOT
 * persist any "rejected" state on the `documents` row (no such column
 * exists in the locked DB-006 schema) — the document simply never reaches
 * `is_approved = true`.
 */
class DocumentApprovalService
{
    public function __construct(private readonly DocumentRepositoryInterface $documents)
    {
    }

    public function approve(string $documentId, string $approvedBy): Document
    {
        $document = $this->documents->find($documentId);

        if (! $document) {
            throw new RuntimeException("Document {$documentId} not found.");
        }

        $document->markApproved($approvedBy);
        $this->documents->save($document);

        DocumentApproved::dispatch($documentId, $approvedBy);

        return $document;
    }

    /**
     * No persistence — see class doc comment's flagged gap. $scanEngine
     * defaults to 'manual' since no real malware/completeness scanner is
     * wired up in this Sprint; a human or future automated check supplies
     * $errorCode describing why.
     */
    public function recordScanFailure(string $documentId, string $errorCode, string $scanEngine = 'manual'): void
    {
        DocumentScanFailed::dispatch($documentId, $scanEngine, $errorCode);
    }
}
