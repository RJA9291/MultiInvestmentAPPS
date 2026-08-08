<?php

namespace App\Modules\AI\Application\Services;

use App\Modules\AI\Domain\Repositories\AiComplianceResultRepositoryInterface;
use App\Modules\AI\Domain\Repositories\InvestorProjectMatchRepositoryInterface;
use App\Modules\AI\Domain\ValueObjects\MatchScoreResult;
use App\Modules\Investor\Domain\Repositories\AccessRequestRepositoryInterface;
use App\Modules\Investor\Domain\Repositories\InvestorProfileRepositoryInterface;
use App\Modules\Project\Domain\Repositories\ProjectRepositoryInterface;

/**
 * AiInvestorMatchingService — orchestrates `MatchScoringCalculator` against
 * real data pulled exclusively through Repository interfaces (PDL-020's
 * interface-only cross-Module dependency, the exact same pattern
 * `ApproveAccessRequestService`/`ManagementDashboardService` already
 * established this Sprint), never the sketch's raw `DB::table('investors')`/
 * `DB::table('projects')` calls.
 *
 * Candidate eligibility deliberately reuses BR-143/BR-144 exactly — the
 * same rules already enforced for Data Room access requests: only a
 * `VERIFIED` InvestorProfile and only a `Published` Project are ever
 * matched. An unverified Investor or an unpublished Project is not
 * "low priority," it is INELIGIBLE — mirroring the Investor Module's own
 * discipline rather than inventing a separate matching-specific rule.
 */
class AiInvestorMatchingService
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly InvestorProfileRepositoryInterface $investorProfiles,
        private readonly AccessRequestRepositoryInterface $accessRequests,
        private readonly AiComplianceResultRepositoryInterface $aiComplianceResults,
        private readonly InvestorProjectMatchRepositoryInterface $matches,
        private readonly MatchScoringCalculator $calculator,
    ) {
    }

    /**
     * @return array<int, MatchScoreResult>
     *
     * Test Scenario 1 (brief §11): "New investor → matches generated."
     * Test Scenario 3: "Profile update → scores updated" — safe to call
     * again any time the profile changes, since `save()` upserts per pair.
     */
    public function matchInvestorAgainstPublishedProjects(string $investorProfileId): array
    {
        $investor = $this->investorProfiles->find($investorProfileId);

        if (! $investor || ! $investor->isVerified()) {
            return []; // BR-143 — an unverified/missing profile is never matched
        }

        $results = [];

        foreach ($this->projects->findPublished() as $project) {
            $result = $this->scoreOne($investor->id(), $investor->investorUserId(), $investor->preferredIndustry(), $investor->riskAppetite(), $project->id(), $project->category());
            $this->matches->save($result);
            $results[] = $result;
        }

        return $results;
    }

    /**
     * @return array<int, MatchScoreResult>
     *
     * Test Scenario 2 (brief §11): "New project → all investors matched."
     */
    public function matchProjectAgainstVerifiedInvestors(string $projectId): array
    {
        $project = $this->projects->find($projectId);

        if (! $project || $project->status()->value !== 'published') {
            return []; // BR-144 — only a Published Project is ever matched
        }

        $results = [];

        foreach ($this->investorProfiles->findAllVerified() as $investor) {
            $result = $this->scoreOne($investor->id(), $investor->investorUserId(), $investor->preferredIndustry(), $investor->riskAppetite(), $project->id(), $project->category());
            $this->matches->save($result);
            $results[] = $result;
        }

        return $results;
    }

    private function scoreOne(
        string $investorProfileId,
        string $investorUserId,
        ?string $preferredIndustry,
        ?string $riskAppetite,
        string $projectId,
        ?string $projectCategory,
    ): MatchScoreResult {
        $hasExistingAccessRequest = $this->investorHasAccessRequestFor($investorUserId, $projectId);
        $projectRiskScore = $this->latestProjectRiskScore($projectId);

        $computed = $this->calculator->calculate(
            investorPreferredIndustry: $preferredIndustry,
            investorRiskAppetite: $riskAppetite,
            projectCategory: $projectCategory,
            projectRiskScore: $projectRiskScore,
            investorHasExistingAccessRequest: $hasExistingAccessRequest,
        );

        return MatchScoreResult::fromComputed(
            investorProfileId: $investorProfileId,
            projectId: $projectId,
            score: $computed['score'],
            breakdown: $computed['breakdown'],
            reasons: $computed['reasons'],
            confidence: $computed['confidence'],
        );
    }

    /**
     * `content_view_counts` (DB-048, Analytics Module) is an AGGREGATE
     * counter, not per-investor — explicitly flagged at build time as not
     * supporting per-viewer breakdown. `AccessRequest` is the one real,
     * precise per-investor-per-project signal already in the schema, so it
     * is used here instead of fabricating an engagement score from data
     * that does not exist.
     */
    private function investorHasAccessRequestFor(string $investorUserId, string $projectId): bool
    {
        foreach ($this->accessRequests->findByProjectId($projectId) as $request) {
            if ($request->investorUserId() === $investorUserId) {
                return true;
            }
        }

        return false;
    }

    private function latestProjectRiskScore(string $projectId): ?int
    {
        $result = $this->aiComplianceResults->findLatestActiveForProject($projectId);

        return $result && $result->available ? $result->riskScore : null;
    }
}
