<?php

namespace App\Modules\Document\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Document\Application\Services\DeleteDocumentService;
use App\Modules\Document\Interfaces\Http\Requests\DeleteDocumentRequest;

/** Proposed API-021: DELETE /v1/documents/{id} — soft delete, BR-032 */
class DeleteDocumentController
{
    public function __invoke(string $id, DeleteDocumentRequest $request, DeleteDocumentService $service)
    {
        $validated = $request->validated();

        $service->execute($id, $validated['deleted_by_user_id']);

        return ApiResponse::success(['document_id' => $id, 'status' => 'deleted'], status: 200);
    }
}
