<?php

namespace App\Modules\Investor\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Investor\Interfaces\Http\Resources\PublishedProjectResource;
use App\Modules\Project\Domain\Repositories\ProjectRepositoryInterface;

/** Proposed API-024: GET /v1/projects (Browse Projects — Investor-facing, Published only) */
class ListPublishedProjectsController
{
    public function __invoke(ProjectRepositoryInterface $projects)
    {
        return ApiResponse::success(PublishedProjectResource::collection($projects->findPublished()));
    }
}
