<?php

namespace App\Modules\AI\Infrastructure\Repositories;

use App\Modules\AI\Domain\Repositories\InvestorProjectMatchRepositoryInterface;
use App\Modules\AI\Domain\ValueObjects\MatchScoreResult;
use App\Modules\AI\Infrastructure\Eloquent\InvestorProjectMatchModel;

class EloquentInvestorProjectMatchRepository implements InvestorProjectMatchRepositoryInterface
{
    public function save(MatchScoreResult $result): void
    {
        if (! $result->available) {
            return; // nothing worth persisting for an unavailable/skipped pair
        }

        InvestorProjectMatchModel::updateOrCreate(
            [
                'investor_profile_id' => $result->investorProfileId,
                'project_id' => $result->projectId,
            ],
            [
                'score' => $result->score,
                'breakdown' => $result->breakdown,
                'reasons' => $result->reasons,
                'confidence' => $result->confidence,
            ],
        );
    }

    public function findTopForInvestor(string $investorProfileId, int $limit): array
    {
        return InvestorProjectMatchModel::where('investor_profile_id', $investorProfileId)
            ->orderByDesc('score')
            ->limit($limit)
            ->get()
            ->map(fn (InvestorProjectMatchModel $model) => $this->toDomain($model))
            ->all();
    }

    public function findTopForProject(string $projectId, int $limit): array
    {
        return InvestorProjectMatchModel::where('project_id', $projectId)
            ->orderByDesc('score')
            ->limit($limit)
            ->get()
            ->map(fn (InvestorProjectMatchModel $model) => $this->toDomain($model))
            ->all();
    }

    private function toDomain(InvestorProjectMatchModel $model): MatchScoreResult
    {
        return MatchScoreResult::fromPersisted(
            investorProfileId: $model->investor_profile_id,
            projectId: $model->project_id,
            score: $model->score,
            breakdown: $model->breakdown ?? [],
            reasons: $model->reasons ?? [],
            confidence: $model->confidence,
        );
    }
}
