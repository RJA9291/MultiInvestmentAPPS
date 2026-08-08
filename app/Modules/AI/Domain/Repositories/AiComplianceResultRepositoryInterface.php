<?php

namespace App\Modules\AI\Domain\Repositories;

use App\Modules\AI\Domain\ValueObjects\AiPrecheckResult;

interface AiComplianceResultRepositoryInterface
{
    /**
     * Latest ACTIVE result for a project, or null if none exists yet.
     * "Only latest ACTIVE result is used" (Project Owner's rule §5) —
     * older rows are never deleted, only ever superseded (see save()).
     */
    public function findLatestActiveForProject(string $projectId): ?AiPrecheckResult;

    /**
     * Persists a NEW result row as ACTIVE and marks any previously-ACTIVE
     * row(s) for the same project as SUPERSEDED. Never updates or deletes
     * an existing row's content — this is an append-only audit trail,
     * mirroring the same pattern already established for `compliance_reviews`
     * (PDL-027/PDL-028), applied here for AI-run audit integrity.
     */
    public function save(string $projectId, AiPrecheckResult $result): void;

    /**
     * Added this Sprint for the Dashboard & Analytics Module's "AI Risk
     * Alerts" / "High Risk Projects" KPI (proposed API-029/030). Counts
     * only ACTIVE rows (never SUPERSEDED, which would double-count a
     * Project across re-runs) with `risk_score` strictly above $threshold.
     */
    public function countActiveAboveRiskThreshold(int $threshold): int;
}
