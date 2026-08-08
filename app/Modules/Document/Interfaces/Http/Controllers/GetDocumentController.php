<?php

namespace App\Modules\Document\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Analytics\Domain\Events\DocumentViewed;
use App\Modules\Document\Domain\Repositories\DocumentRepositoryInterface;
use App\Modules\Document\Interfaces\Http\Resources\DocumentResource;

/**
 * Proposed API-017: GET /v1/documents/{id}.
 *
 * Dispatches `DocumentViewed` (EVT-074, Analytics Module) on every
 * successful fetch, mirroring `GetProjectController`'s `ProjectViewed`
 * wiring — interface-only, fire-and-forget (PDL-020).
 */
class GetDocumentController
{
    public function __invoke(string $id, DocumentRepositoryInterface $documents)
    {
        $document = $documents->find($id);

        if (! $document) {
            return ApiResponse::error('DOCUMENT_NOT_FOUND', "Document {$id} not found.", status: 404);
        }

        DocumentViewed::dispatch($id, null);

        return ApiResponse::success(new DocumentResource($document));
    }
}
