<?php

namespace App\Modules\AI\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\AI\Application\Services\ComplianceAssistantService;
use App\Modules\AI\Domain\Repositories\AiComplianceResultRepositoryInterface;
use App\Modules\AI\Domain\ValueObjects\AiPrecheckResult;
use App\Modules\Compliance\Domain\Repositories\ComplianceReviewRepositoryInterface;
use Illuminate\Http\Request;

/**
 * API-012 (12_API_STANDARD.md v1.2.0, §15 — locked as Draft):
 * POST /v1/projects/{id}/ai-precheck — {id} is the PROJECT id.
 *
 * Fetch-or-run: RunAiCompliancePrecheckOnSubmit already triggers a pre-check
 * automatically on every ProjectSubmitted/ProjectResubmitted event, so in
 * the common case this endpoint just serves the latest stored ACTIVE
 * ai_compliance_results row (fast, deterministic — no repeated provider
 * call for the same submission cycle). If no stored result exists yet
 * (e.g. called before the queued listener has run, or before any submission
 * at all), it runs preCheck() on demand instead.
 *
 * WAJIB: this endpoint is read-only and advisory (PDL-053, PDL-058) — it
 * never accepts or produces an approve/reject decision, and its response is
 * always labeled "AI Recommendation" (enforced inside AiPrecheckResult's
 * toApiPayload(), not here). That decision remains
 * DecideComplianceReviewController's exclusive responsibility (API-004).
 * API-012 must never trigger, queue, or otherwise cause the Approval/
 * Rejection flow — this Controller has no dependency capable of doing so.
 */
class ComplianceAssistantPreCheckController
{
    public function __invoke(
        string $id,
        Request $request,
        ComplianceAssistantService $assistant,
        AiComplianceResultRepositoryInterface $results,
        ComplianceReviewRepositoryInterface $reviews,
    ) {
        $activeReview = $reviews->findActiveCycleForProject($id);

        if (! $activeReview) {
            return ApiResponse::success(
                AiPrecheckResult::unavailable('No active Compliance Review cycle exists for this project.')
                    ->toApiPayload()
            );
        }

        $stored = $results->findLatestActiveForProject($id);

        if ($stored) {
            return ApiResponse::success($stored->toApiPayload());
        }

        $documentTypes = $request->input('document_types_present', []);

        $result = $assistant->preCheck($id, is_array($documentTypes) ? $documentTypes : []);

        return ApiResponse::success($result->toApiPayload());
    }
}
