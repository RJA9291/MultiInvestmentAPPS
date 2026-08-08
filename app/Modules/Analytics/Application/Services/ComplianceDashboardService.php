<?php

namespace App\Modules\Analytics\Application\Services;

use App\Modules\AI\Domain\Repositories\AiComplianceResultRepositoryInterface;
use App\Modules\AI\Domain\Repositories\AiDocumentVerificationResultRepositoryInterface;
use App\Modules\Compliance\Domain\Repositories\ComplianceReviewRepositoryInterface;
use App\Modules\Project\Domain\Repositories\ProjectRepositoryInterface;

/**
 * Proposed API-030: GET /v1/dashboard/compliance.
 *
 * Same Repository-only reconciliation as `ManagementDashboardService`.
 * "Pending approvals" is answered from `compliance_reviews.status = pending`
 * (DB-033/BR-140) — the real, already-locked source of truth for a
 * Compliance Officer's queue — rather than the sketch's ambiguous
 * `projects.status = 'PENDING'` (no such enum value exists; the closest
 * real state is `under_compliance_review`). "Document issues" maps to
 * `ai_document_verifications` rows carrying a non-empty `issues`/
 * `risk_flags` array (DB-045) — the Document Module itself has no
 * "flagged" concept of its own (Content §3.2's `is_approved` boolean is a
 * human decision, not an issue log).
 */
class ComplianceDashboardService
{
    private const HIGH_RISK_THRESHOLD = 80;

    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly AiComplianceResultRepositoryInterface $aiComplianceResults,
        private readonly ComplianceReviewRepositoryInterface $complianceReviews,
        private readonly AiDocumentVerificationResultRepositoryInterface $aiDocumentVerifications,
    ) {
    }

    /** @return array<string, int> */
    public function getMetrics(): array
    {
        return [
            'projects_under_review' => $this->projects->countByStatuses(['under_compliance_review']),
            'high_risk_projects' => $this->aiComplianceResults->countActiveAboveRiskThreshold(self::HIGH_RISK_THRESHOLD),
            'pending_approvals' => $this->complianceReviews->countByStatus('pending'),
            'document_issues' => $this->aiDocumentVerifications->countActiveWithIssues(),
        ];
    }
}
