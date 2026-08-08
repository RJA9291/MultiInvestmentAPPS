<?php

namespace App\Modules\Project\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Analytics\Domain\Events\ProjectViewed;
use App\Modules\Project\Domain\Repositories\ProjectRepositoryInterface;
use App\Modules\Project\Interfaces\Http\Resources\ProjectResource;

/**
 * API-002: GET /v1/projects/{id}.
 *
 * Dispatches `ProjectViewed` (EVT-073, Analytics Module, added Sprint 12
 * for the Dashboard & Analytics Module brief) on every successful fetch —
 * an interface-only, fire-and-forget cross-Module signal (PDL-020), not a
 * dependency on the Analytics Module itself.
 */
class GetProjectController
{
    public function __invoke(string $id, ProjectRepositoryInterface $projects)
    {
        $project = $projects->find($id);

        if (! $project) {
            return ApiResponse::error('PROJECT_NOT_FOUND', 'Project does not exist', status: 404);
        }

        ProjectViewed::dispatch($id, null);

        return ApiResponse::success(new ProjectResource($project));
    }
}
