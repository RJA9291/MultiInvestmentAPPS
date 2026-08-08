<?php

namespace App\Modules\Investor\Application\Services;

use App\Modules\Investor\Domain\Entities\AccessRequest;
use App\Modules\Investor\Domain\Events\AccessRequested;
use App\Modules\Investor\Domain\Repositories\AccessRequestRepositoryInterface;
use App\Modules\Investor\Domain\Repositories\InvestorProfileRepositoryInterface;
use App\Modules\Investor\Domain\ValueObjects\AccessRequestStatus;
use App\Modules\Project\Domain\Repositories\ProjectRepositoryInterface;
use App\Modules\Project\Domain\ValueObjects\PublishState;
use DomainException;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * RequestProjectAccessService — proposed API-025 (POST /v1/projects/{id}/request-access).
 *
 * Two gates the Project Owner's brief's flow diagram implies ("Investor
 * Verified -> Browse Project -> Request Access") but its code sketch never
 * actually enforced — added here as real checks, flagged as new rules in
 * `04_BUSINESS_RULES.md` rather than silently assumed:
 *  1. Only a VERIFIED investor profile may request access (an unverified/
 *     rejected investor calling this endpoint is rejected outright, not
 *     silently allowed to create a PENDING request nobody should review).
 *  2. Only a Published Project may receive an access request — Draft/
 *     UnderComplianceReview/etc. projects are not yet fit for external
 *     investor visibility (mirrors BR-XXX's "Browse Projects" restriction).
 * Also guards against duplicate PENDING requests for the same
 * (project, investor) pair — a real UNIQUE constraint was deliberately not
 * used (see migration's doc comment), so this is an application-layer check.
 */
class RequestProjectAccessService
{
    public function __construct(
        private readonly AccessRequestRepositoryInterface $accessRequests,
        private readonly InvestorProfileRepositoryInterface $investorProfiles,
        private readonly ProjectRepositoryInterface $projects,
    ) {
    }

    public function execute(string $projectId, string $investorUserId): AccessRequest
    {
        $project = $this->projects->find($projectId);

        if (! $project) {
            throw new RuntimeException("Project {$projectId} not found.");
        }

        if ($project->status() !== PublishState::Published) {
            throw new DomainException("Project {$projectId} is not open for access requests (must be Published).");
        }

        $investorProfile = $this->investorProfiles->findByInvestorUserId($investorUserId);

        if (! $investorProfile || ! $investorProfile->isVerified()) {
            throw new DomainException("Investor {$investorUserId} must have a VERIFIED profile before requesting access.");
        }

        $hasPending = array_filter(
            $this->accessRequests->findByProjectId($projectId),
            fn (AccessRequest $r) => $r->investorUserId() === $investorUserId && $r->status() === AccessRequestStatus::Pending,
        );

        if (! empty($hasPending)) {
            throw new DomainException("Investor {$investorUserId} already has a pending access request for project {$projectId}.");
        }

        $request = AccessRequest::request((string) Str::uuid(), $projectId, $investorUserId);
        $this->accessRequests->save($request);

        AccessRequested::dispatch($request->id(), $projectId, $investorUserId);

        return $request;
    }
}
