<?php

namespace App\Modules\Document\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Document\Application\Services\UploadDocumentService;
use App\Modules\Document\Interfaces\Http\Requests\UploadDocumentRequest;
use App\Modules\Document\Interfaces\Http\Resources\DocumentResource;
use DomainException;

/** API-010: POST /v1/documents (locked path) */
class UploadDocumentController
{
    public function __invoke(UploadDocumentRequest $request, UploadDocumentService $service)
    {
        $validated = $request->validated();
        $file = $validated['file'];

        try {
            $document = $service->execute(
                projectId: $validated['project_id'],
                documentType: $validated['document_type'],
                originalFileName: $file->getClientOriginalName(),
                mimeType: $file->getMimeType(),
                contents: $file->get(),
                uploadedBy: $validated['uploaded_by_user_id'],
            );
        } catch (DomainException $e) {
            return ApiResponse::error('DOCUMENT_TYPE_NOT_ALLOWED', $e->getMessage(), status: 422);
        }

        return ApiResponse::success(new DocumentResource($document), status: 201);
    }
}
