<?php

namespace App\Modules\Project\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Project\Application\Services\ProjectPublishingService;
use App\Modules\Project\Interfaces\Http\Resources\ProjectResource;
use DomainException;

/** API-003: POST /v1/projects/{id}/submit — WAJIB Idempotency-Key (API-IDEM-001). */
class SubmitProjectController
{
    public function __invoke(string $id, ProjectPublishingService $service)
    {
        try {
            $project = $service->submit($id);
        } catch (DomainException $e) {
            return ApiResponse::error('PROJECT_INVALID_TRANSITION', $e->getMessage(), status: 409);
        }

        return ApiResponse::success(new ProjectResource($project));
    }
}
