<?php

namespace App\Modules\Document\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Document\Application\Services\DocumentApprovalService;
use App\Modules\Document\Interfaces\Http\Requests\ApproveDocumentRequest;
use App\Modules\Document\Interfaces\Http\Resources\DocumentResource;
use DomainException;
use RuntimeException;

/**
 * Proposed API-019: POST /v1/documents/{id}/approve
 *
 * "Approved" here means the automated malware/type/completeness checks
 * passed (06_DOMAIN_MODEL.md §3.2) — distinct from the human Project-level
 * ComplianceReview decision, already built separately.
 */
class ApproveDocumentController
{
    public function __invoke(string $id, ApproveDocumentRequest $request, DocumentApprovalService $service)
    {
        $validated = $request->validated();

        try {
            $document = $service->approve($id, $validated['approved_by_user_id']);
        } catch (RuntimeException $e) {
            return ApiResponse::error('DOCUMENT_NOT_FOUND', $e->getMessage(), status: 404);
        } catch (DomainException $e) {
            return ApiResponse::error('DOCUMENT_ALREADY_APPROVED', $e->getMessage(), status: 409);
        }

        return ApiResponse::success(new DocumentResource($document));
    }
}
