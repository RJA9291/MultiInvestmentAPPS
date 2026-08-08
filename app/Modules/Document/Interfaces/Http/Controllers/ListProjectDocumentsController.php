<?php

namespace App\Modules\Document\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Document\Domain\Repositories\DocumentRepositoryInterface;
use App\Modules\Document\Interfaces\Http\Resources\DocumentResource;

/** Proposed API-016: GET /v1/projects/{projectId}/documents */
class ListProjectDocumentsController
{
    public function __invoke(string $projectId, DocumentRepositoryInterface $documents)
    {
        return ApiResponse::success(DocumentResource::collection($documents->findByProjectId($projectId)));
    }
}
