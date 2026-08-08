<?php

use App\Modules\AI\Interfaces\Http\Controllers\ComplianceAssistantPreCheckController;
use App\Modules\AI\Interfaces\Http\Controllers\GetInvestorMatchesController;
use App\Modules\AI\Interfaces\Http\Controllers\GetProjectMatchesController;
use App\Modules\Analytics\Interfaces\Http\Controllers\ComplianceDashboardController;
use App\Modules\Analytics\Interfaces\Http\Controllers\ManagementDashboardController;
use App\Modules\Analytics\Interfaces\Http\Controllers\ProjectOwnerDashboardController;
use App\Modules\Compliance\Interfaces\Http\Controllers\DecideComplianceReviewController;
use App\Modules\DataRoom\Interfaces\Http\Controllers\AccessDocumentController;
use App\Modules\DataRoom\Interfaces\Http\Controllers\AcknowledgeNdaController;
use App\Modules\DataRoom\Interfaces\Http\Controllers\DownloadDocumentController;
use App\Modules\DataRoom\Interfaces\Http\Controllers\GrantDataRoomAccessController;
use App\Modules\DataRoom\Interfaces\Http\Controllers\RevokeDataRoomAccessController;
use App\Modules\Document\Interfaces\Http\Controllers\ApproveDocumentController;
use App\Modules\Document\Interfaces\Http\Controllers\DeleteDocumentController;
use App\Modules\Document\Interfaces\Http\Controllers\GetDocumentController;
use App\Modules\Document\Interfaces\Http\Controllers\ListProjectDocumentsController;
use App\Modules\Document\Interfaces\Http\Controllers\RejectDocumentController;
use App\Modules\Document\Interfaces\Http\Controllers\UploadDocumentController;
use App\Modules\Document\Interfaces\Http\Controllers\UploadDocumentVersionController;
use App\Modules\Identity\Interfaces\Http\Controllers\LoginController;
use App\Modules\Identity\Interfaces\Http\Controllers\LogoutController;
use App\Modules\Identity\Interfaces\Http\Controllers\MeController;
use App\Modules\Investor\Interfaces\Http\Controllers\ApproveAccessRequestController;
use App\Modules\Investor\Interfaces\Http\Controllers\ListPublishedProjectsController;
use App\Modules\Investor\Interfaces\Http\Controllers\RegisterInvestorProfileController;
use App\Modules\Investor\Interfaces\Http\Controllers\RejectAccessRequestController;
use App\Modules\Investor\Interfaces\Http\Controllers\RejectInvestorController;
use App\Modules\Investor\Interfaces\Http\Controllers\RequestProjectAccessController;
use App\Modules\Investor\Interfaces\Http\Controllers\VerifyInvestorController;
use App\Modules\Notification\Interfaces\Http\Controllers\ListNotificationsController;
use App\Modules\Notification\Interfaces\Http\Controllers\MarkNotificationAsReadController;
use App\Modules\Project\Interfaces\Http\Controllers\CreateProjectController;
use App\Modules\Project\Interfaces\Http\Controllers\GetProjectController;
use App\Modules\Project\Interfaces\Http\Controllers\PublishProjectController;
use App\Modules\Project\Interfaces\Http\Controllers\SubmitProjectController;
use Illuminate\Support\Facades\Route;

/**
 * 12_API_STANDARD.md §1 — URL-based versioning, /v1/ prefix.
 * WAJIB middleware order (14_LARAVEL_BLUEPRINT.md §9): request-id runs first,
 * unconditionally, before auth/rate-limit/authz/idempotency/logging.
 *
 * The Identity Module now exists (API-034/035/036 below) and `jwt.auth`
 * (JwtAuthenticate, BR-003) is real — every route except `/v1/auth/login`
 * is wrapped in it. `throttle`, RBAC/ABAC beyond the coarse `role:` alias,
 * and `idempotency` are still NOT implemented in this pass — flagged, not
 * fabricated, same discipline as every other open item this Sprint.
 *
 * Fine-grained per-endpoint role gating (e.g. only a Compliance Officer may
 * call DecideComplianceReviewController) is intentionally NOT retrofitted
 * onto the pre-existing routes below in this pass — a mechanical follow-up
 * (adding `role:x` to specific lines), not bundled into the Identity build
 * itself so the two changes stay reviewable separately.
 */
Route::prefix('v1')
    ->middleware(['request-id']) // PDL-044 — WAJIB, always active
    ->group(function () {
        // API-034: no auth required to obtain one
        Route::post('/auth/login', LoginController::class);
    });

Route::prefix('v1')
    ->middleware(['request-id', 'jwt.auth']) // BR-003, enforced platform-wide from here down
    ->group(function () {
        // API-035, API-036
        Route::post('/auth/logout', LogoutController::class);
        Route::get('/auth/me', MeController::class);

        // API-001, API-002, API-003 (12_API_STANDARD.md §15)
        Route::post('/projects', CreateProjectController::class);
        Route::get('/projects/{id}', GetProjectController::class);
        Route::post('/projects/{id}/submit', SubmitProjectController::class);

        // API-011 (12_API_STANDARD.md v1.2.0 §15 — locked as Draft)
        Route::post('/projects/{id}/publish', PublishProjectController::class);

        // API-012 (12_API_STANDARD.md v1.2.0 §15 — locked as Draft, Project-scoped
        // per Project Owner review; {id} here is the PROJECT id, not a
        // ComplianceReview id). Advisory pre-check only — PDL-053/PDL-058.
        Route::post('/projects/{id}/ai-precheck', ComplianceAssistantPreCheckController::class);

        // API-004 (12_API_STANDARD.md §15)
        Route::post('/compliance-reviews/{id}/decide', DecideComplianceReviewController::class);

        // API-005 (12_API_STANDARD.md §15 — locked path, NOT the Project
        // Owner's originally sketched /v1/data-room/grant)
        Route::post('/data-room-grants', GrantDataRoomAccessController::class);

        // Proposed API-013/API-014 — flagged, not yet in the API Registry
        Route::patch('/data-room-grants/{id}/revoke', RevokeDataRoomAccessController::class);
        Route::post('/data-room-grants/{id}/nda-acknowledgment', AcknowledgeNdaController::class);

        // API-006 (12_API_STANDARD.md §15 — locked path; {id} is the PROJECT
        // id, kept for routing context, even though the grant check itself
        // is per-document+user, not per-project)
        Route::get('/projects/{id}/documents/{docId}', AccessDocumentController::class);

        // Proposed API-015 — flagged, not yet in the API Registry
        Route::get('/projects/{id}/documents/{docId}/download', DownloadDocumentController::class);

        // API-010 (12_API_STANDARD.md §15 — locked path, `POST /v1/documents`,
        // NOT the Project Owner's originally sketched /v1/documents/upload)
        Route::post('/documents', UploadDocumentController::class);

        // Proposed API-016/API-017 — flagged, not yet in the API Registry
        Route::get('/projects/{id}/documents', ListProjectDocumentsController::class);
        Route::get('/documents/{id}', GetDocumentController::class);

        // Proposed API-018/API-019/API-020/API-021 — flagged, not yet in the API Registry
        Route::post('/documents/{id}/versions', UploadDocumentVersionController::class);
        Route::post('/documents/{id}/approve', ApproveDocumentController::class);
        Route::post('/documents/{id}/reject', RejectDocumentController::class);
        Route::delete('/documents/{id}', DeleteDocumentController::class);

        // Investor Module — proposed API-022/API-023, flagged, not yet in the
        // API Registry. NOTE: "register" here creates the investment-specific
        // InvestorProfile extension only — real account registration
        // (email/password) is Identity Context's job, not yet built (same
        // flagged gap as every `*_user_id` field this Sprint).
        Route::post('/investors/register', RegisterInvestorProfileController::class);
        Route::post('/investors/{id}/verify', VerifyInvestorController::class);
        Route::post('/investors/{id}/reject', RejectInvestorController::class);

        // Proposed API-024 — "Browse Projects" (Investor-facing, Published only).
        Route::get('/projects', ListPublishedProjectsController::class);

        // Proposed API-025/API-026 — flagged, not yet in the API Registry.
        Route::post('/projects/{id}/request-access', RequestProjectAccessController::class);
        Route::post('/access-requests/{id}/approve', ApproveAccessRequestController::class);
        Route::post('/access-requests/{id}/reject', RejectAccessRequestController::class);

        // Notification Module — proposed API-027/API-028, flagged, not yet
        // in the API Registry. `recipient_user_id`/`requesting_user_id` are
        // request-supplied stand-ins pending Identity Module (same pattern
        // as every other Module's `*_user_id` field this Sprint) — a real
        // implementation would derive these from the authenticated session.
        Route::get('/notifications', ListNotificationsController::class);
        Route::post('/notifications/{id}/read', MarkNotificationAsReadController::class);

        // Dashboard & Analytics Module — proposed API-029/030/031, flagged,
        // not yet in the API Registry. BR-148 (role-based access) and
        // BR-149 (no cross-project leakage) are documented but NOT enforced
        // at this route-group level — depends on the same not-yet-built
        // Identity Module `authorize.rbac` middleware every other route in
        // this file already flags above.
        Route::get('/dashboard/management', ManagementDashboardController::class);
        Route::get('/dashboard/compliance', ComplianceDashboardController::class);
        Route::get('/dashboard/project/{projectId}', ProjectOwnerDashboardController::class);

        // AI Investor-Project Matching Engine — proposed API-032/033,
        // flagged, not yet in the API Registry. {id} on the first route is
        // an InvestorProfile id; on the second, a Project id.
        Route::get('/investors/{id}/matches', GetInvestorMatchesController::class);
        Route::get('/projects/{id}/matches', GetProjectMatchesController::class);
    });
