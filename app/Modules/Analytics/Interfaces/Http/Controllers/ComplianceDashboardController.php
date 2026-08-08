<?php

namespace App\Modules\Analytics\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Analytics\Application\Services\ComplianceDashboardService;

/** Proposed API-030: GET /v1/dashboard/compliance. BR-148 role gate flagged, same as ManagementDashboardController. */
class ComplianceDashboardController
{
    public function __invoke(ComplianceDashboardService $dashboard)
    {
        return ApiResponse::success($dashboard->getMetrics());
    }
}
