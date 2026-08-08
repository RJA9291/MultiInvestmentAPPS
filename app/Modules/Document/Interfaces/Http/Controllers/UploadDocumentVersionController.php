<?php

namespace App\Modules\Document\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Document\Application\Services\DocumentVersioningService;
use App\Modules\Document\Domain\Repositories\DocumentRepositoryInterface;
use App\Modules\Document\Interfaces\Http\Requests\UploadDocumentVersionRequest;
use App\Modules\Document\Interfaces\Http\Resources\DocumentVersionResource;
use DomainException;

/**
 * Proposed API-018: POST /v1/documents/{id}/versions
 *
 * Requires the caller to supply the existing `document_id` (route param) —
 * NOT the Project Owner's "same name + project_id" matching, since DB-006
 * has no such Business Key (see DocumentVersioningService doc comment).
 */
class UploadDocumentVersionController
{
    public function __invoke(
        string $id,
        UploadDocumentVersionRequest $request,
        DocumentVersioningService $service,
        DocumentRepositoryInterface $documents,
    ) {
        if (! $documents->find($id)) {
            return ApiResponse::error('DOCUMENT_NOT_FOUND', "Document {$id} not found.", status: 404);
        }

        $validated = $request->validated();
        $file = $validated['file'];

        try {
            $version = $service->uploadNewVersion(
                documentId: $id,
                originalFileName: $file->getClientOriginalName(),
                mimeType: $file->getMimeType(),
                contents: $file->get(),
                createdBy: $validated['created_by_user_id'],
            );
        } catch (DomainException $e) {
            return ApiResponse::error('DOCUMENT_VERSION_REJECTED', $e->getMessage(), status: 422);
        }

        return ApiResponse::success(new DocumentVersionResource($version), status: 201);
    }
}
