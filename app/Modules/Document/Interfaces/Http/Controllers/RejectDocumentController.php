<?php

namespace App\Modules\Document\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Document\Application\Services\DocumentApprovalService;
use App\Modules\Document\Interfaces\Http\Requests\RejectDocumentRequest;

/**
 * Proposed API-020: POST /v1/documents/{id}/reject
 *
 * Named "reject" for API familiarity with the Project Owner's brief, but
 * internally maps to the locked catalog's real negative-counterpart event,
 * DocumentScanFailed (EVT-020) — there is no DocumentRejected event in
 * 07_EVENT_CATALOG.md. No dedicated status is persisted for this beyond the
 * event itself, matching the locked schema (is_approved simply stays false).
 */
class RejectDocumentController
{
    public function __invoke(string $id, RejectDocumentRequest $request, DocumentApprovalService $service)
    {
        $validated = $request->validated();

        $service->recordScanFailure($id, $validated['error_code']);

        return ApiResponse::success(['document_id' => $id, 'status' => 'scan_failed']);
    }
}
