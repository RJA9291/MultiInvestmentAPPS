<?php

namespace App\Modules\Analytics\Application\Services;

use App\Modules\AI\Domain\Repositories\AiComplianceResultRepositoryInterface;
use App\Modules\Document\Domain\Repositories\DocumentRepositoryInterface;
use App\Modules\Investor\Domain\Repositories\AccessRequestRepositoryInterface;

/**
 * Proposed API-031: GET /v1/dashboard/project/{projectId}.
 *
 * Unlike the two platform-wide dashboards, every source Repository here
 * already exposes exactly the per-project query this dashboard needs
 * (`findByProjectId()`, `findLatestActiveForProject()`) — no new additive
 * count method was required for this one. "Investor interest" and "access
 * requests" are the same underlying data (the sketch's brief listed them
 * as two separate bullets; `AccessRequest`, Investment Context, is the one
 * Aggregate behind both).
 *
 * BR-149 (04_BUSINESS_RULES.md): this Service returns data for exactly the
 * one $projectId it is given — it never joins across other Projects. The
 * Controller is responsible for verifying the requesting user actually
 * owns $projectId before calling this Service; that ownership check itself
 * depends on the Identity Module's real auth (not yet built, same flagged
 * gap as every other Module this Sprint) and is NOT enforced here yet.
 */
class ProjectOwnerDashboardService
{
    public function __construct(
        private readonly DocumentRepositoryInterface $documents,
        private readonly AccessRequestRepositoryInterface $accessRequests,
        private readonly AiComplianceResultRepositoryInterface $aiComplianceResults,
    ) {
    }

    /** @return array<string, mixed> */
    public function getMetrics(string $projectId): array
    {
        $documents = $this->documents->findByProjectId($projectId);
        $accessRequests = $this->accessRequests->findByProjectId($projectId);
        $aiFeedback = $this->aiComplianceResults->findLatestActiveForProject($projectId);

        return [
            'document_status' => [
                'total' => count($documents),
                'approved' => count(array_filter($documents, fn ($document) => $document->isApproved())),
                'pending' => count(array_filter($documents, fn ($document) => ! $document->isApproved())),
            ],
            'investor_interest' => [
                'access_requests_total' => count($accessRequests),
                'access_requests_approved' => count(array_filter(
                    $accessRequests,
                    fn ($request) => $request->status()->value === 'APPROVED'
                )),
            ],
            'access_requests' => count($accessRequests),
            'ai_feedback' => $aiFeedback && $aiFeedback->available ? $aiFeedback->toApiPayload() : null,
        ];
    }
}
