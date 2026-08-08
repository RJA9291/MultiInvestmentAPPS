<?php

namespace App\Modules\Analytics\Application\Services;

use App\Modules\AI\Domain\Repositories\AiComplianceResultRepositoryInterface;
use App\Modules\Investor\Domain\Repositories\InvestorProfileRepositoryInterface;
use App\Modules\Project\Domain\Repositories\ProjectRepositoryInterface;

/**
 * Proposed API-029: GET /v1/dashboard/management.
 *
 * RECONCILIATION vs. the Project Owner's sketch: `DashboardService`'s own
 * `getManagementMetrics()` used raw `DB::table('projects')`, `DB::table
 * ('investors')`, `DB::table('ai_compliance_results')` — direct
 * cross-Module database access, forbidden outright by
 * `14_LARAVEL_BLUEPRINT.md` §9 ("Direct database access bypassing a
 * Repository"). Every count below instead goes through the OWNING
 * Module's own Repository interface (PDL-020's interface-only allowance),
 * via small additive count methods added this Sprint — the same additive
 * pattern already used for `ProjectRepositoryInterface::findPublished()`
 * (Investor Module). This class contains no business logic of its own
 * (PDL-021) — it only aggregates numbers that are already true elsewhere.
 *
 * The sketch's `investors` table and `status = 'PENDING'` string literal
 * are also both stale against the locked schema: the real table is
 * `investor_profiles` (DB-046) with `verification_status` (PENDING |
 * VERIFIED | REJECTED, uppercase), and the real `projects.status`
 * (DB-004) enum is lowercase (`draft`, `submitted`,
 * `under_compliance_review`, ...) — corrected here directly, per the same
 * "DBR-001 is unambiguous, don't re-ask" precedent used throughout this
 * Sprint for other schema corrections.
 */
class ManagementDashboardService
{
    /** Matches the sketch's own `risk_score > 80` threshold (0-100 scale, ai_compliance_results.risk_score). */
    private const HIGH_RISK_THRESHOLD = 80;

    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly InvestorProfileRepositoryInterface $investorProfiles,
        private readonly AiComplianceResultRepositoryInterface $aiComplianceResults,
    ) {
    }

    /** @return array<string, int> */
    public function getMetrics(): array
    {
        return [
            'total_projects' => $this->projects->countAll(),
            'pending_projects' => $this->projects->countByStatuses(['submitted', 'under_compliance_review']),
            'active_investors' => $this->investorProfiles->countByVerificationStatus('VERIFIED'),
            'ai_risk_alerts' => $this->aiComplianceResults->countActiveAboveRiskThreshold(self::HIGH_RISK_THRESHOLD),
        ];
    }
}
