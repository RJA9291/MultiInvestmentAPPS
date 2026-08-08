<?php

namespace App\Modules\Analytics\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Analytics\Application\Services\ProjectOwnerDashboardService;

/**
 * Proposed API-031: GET /v1/dashboard/project/{projectId}.
 *
 * BR-149: dashboard data is scoped to exactly this one $projectId. Real
 * ownership enforcement (only the owning Business Owner or a Compliance
 * Officer may view it) depends on the Identity Module's real auth/session
 * (not yet built) — flagged, not fabricated, same as every other Module's
 * auth gap this Sprint. Deliberately does NOT dispatch `ProjectViewed` —
 * that event models a viewer looking at the Project's own record
 * (`GetProjectController`), a distinct business moment from an Owner
 * checking their dashboard's aggregate stats.
 */
class ProjectOwnerDashboardController
{
    public function __invoke(string $projectId, ProjectOwnerDashboardService $dashboard)
    {
        return ApiResponse::success($dashboard->getMetrics($projectId));
    }
}
