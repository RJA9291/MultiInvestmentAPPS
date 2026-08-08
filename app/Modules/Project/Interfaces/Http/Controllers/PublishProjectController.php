<?php

namespace App\Modules\Project\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Project\Application\Services\ProjectPublishingService;
use App\Modules\Project\Interfaces\Http\Requests\PublishProjectRequest;
use App\Modules\Project\Interfaces\Http\Resources\ProjectResource;
use DomainException;

/**
 * Proposed API-011: POST /v1/projects/{id}/publish
 * FLAGGED: not yet in the API Registry (12_API_STANDARD.md §15) — see
 * PublishProjectRequest's doc comment. Registered as Draft in the
 * api_registry seed data, not Active, per PDL-041.
 */
class PublishProjectController
{
    public function __invoke(string $id, PublishProjectRequest $request, ProjectPublishingService $service)
    {
        try {
            $project = $service->publish($id, $request->validated()['approved_document_count']);
        } catch (DomainException $e) {
            return ApiResponse::error('PROJECT_NOT_PUBLISHABLE', $e->getMessage(), status: 409);
        }

        return ApiResponse::success(new ProjectResource($project));
    }
}
