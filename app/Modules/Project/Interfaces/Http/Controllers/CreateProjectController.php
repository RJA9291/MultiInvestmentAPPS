<?php

namespace App\Modules\Project\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Project\Application\Services\ProjectPublishingService;
use App\Modules\Project\Interfaces\Http\Requests\CreateProjectRequest;
use App\Modules\Project\Interfaces\Http\Resources\ProjectResource;

/** API-001: POST /v1/projects — thin Controller, no business logic (14_LARAVEL_BLUEPRINT.md §4). */
class CreateProjectController
{
    public function __invoke(CreateProjectRequest $request, ProjectPublishingService $service)
    {
        $validated = $request->validated();

        $project = $service->create(
            ownerUserId: $validated['owner_user_id'],
            title: $validated['title'],
            description: $validated['description'] ?? null,
            category: $validated['category'] ?? null,
        );

        return ApiResponse::success(new ProjectResource($project), status: 201);
    }
}
