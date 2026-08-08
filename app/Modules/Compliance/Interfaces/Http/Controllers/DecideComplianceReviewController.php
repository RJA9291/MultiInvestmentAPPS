<?php

namespace App\Modules\Compliance\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Compliance\Application\Services\ComplianceDecisionService;
use App\Modules\Compliance\Domain\Repositories\ComplianceReviewRepositoryInterface;
use App\Modules\Compliance\Interfaces\Http\Requests\DecideComplianceReviewRequest;
use App\Modules\Compliance\Interfaces\Http\Resources\ComplianceReviewResource;
use DomainException;

/**
 * API-004: POST /v1/compliance-reviews/{id}/decide — WAJIB Idempotency-Key.
 * This Controller (and the Service it calls) is the ONLY path that can move
 * a ComplianceReview out of Pending. No AI code has any route into this.
 */
class DecideComplianceReviewController
{
    public function __invoke(
        string $id,
        DecideComplianceReviewRequest $request,
        ComplianceDecisionService $decisionService,
        ComplianceReviewRepositoryInterface $reviews,
    ) {
        $validated = $request->validated();

        try {
            $decisionService->decide(
                reviewId: $id,
                decision: $validated['decision'],
                actingUserRole: $validated['acting_user_role'],
                actingUserId: $validated['acting_user_id'],
                comments: $validated['comments'] ?? [],
                decisionSource: $validated['decision_source'] ?? 'HUMAN',
            );
        } catch (DomainException $e) {
            return ApiResponse::error('COMPLIANCE_DECISION_REJECTED', $e->getMessage(), status: 422);
        }

        $review = $reviews->find($id);

        return ApiResponse::success(new ComplianceReviewResource($review));
    }
}
