<?php

namespace App\Modules\AI\Domain\Repositories;

use App\Modules\AI\Domain\ValueObjects\MatchScoreResult;

interface InvestorProjectMatchRepositoryInterface
{
    /** One row per (investor_profile_id, project_id) pair — `save()` upserts, mirroring the sketch's own `updateOrInsert` intent via the Repository pattern instead of raw DB access. */
    public function save(MatchScoreResult $result): void;

    /**
     * @return array<int, MatchScoreResult> ordered by score descending
     *
     * Proposed API-032: GET /v1/investors/{id}/matches — Investor-facing.
     */
    public function findTopForInvestor(string $investorProfileId, int $limit): array;

    /**
     * @return array<int, MatchScoreResult> ordered by score descending
     *
     * Proposed API-033: GET /v1/projects/{id}/matches — Project-Owner-facing
     * "Potential Investors" view (brief §8).
     */
    public function findTopForProject(string $projectId, int $limit): array;
}
