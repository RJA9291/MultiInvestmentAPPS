<?php

namespace App\Modules\Analytics\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Analytics\Application\Services\ManagementDashboardService;

/**
 * Proposed API-029: GET /v1/dashboard/management.
 *
 * BR-148: production deployment must gate this route behind
 * `authorize.rbac` (Management/Admin role only) — not implemented in this
 * pass, same flagged gap as every route in `routes/api.php`'s own
 * commented-out `auth:sanctum`/`authorize.rbac` middleware stack, since
 * the Identity Module's real roles/session do not exist yet.
 */
class ManagementDashboardController
{
    public function __invoke(ManagementDashboardService $dashboard)
    {
        return ApiResponse::success($dashboard->getMetrics());
    }
}
