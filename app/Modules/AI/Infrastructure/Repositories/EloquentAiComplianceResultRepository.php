<?php

namespace App\Modules\AI\Infrastructure\Repositories;

use App\Modules\AI\Domain\Repositories\AiComplianceResultRepositoryInterface;
use App\Modules\AI\Domain\ValueObjects\AiPrecheckResult;
use App\Modules\AI\Infrastructure\Eloquent\AiComplianceResultModel;
use Illuminate\Support\Facades\DB;

class EloquentAiComplianceResultRepository implements AiComplianceResultRepositoryInterface
{
    public function findLatestActiveForProject(string $projectId): ?AiPrecheckResult
    {
        $row = AiComplianceResultModel::where('project_id', $projectId)
            ->where('status', 'ACTIVE')
            ->latest('created_at')
            ->first();

        if (! $row) {
            return null;
        }

        return AiPrecheckResult::fromPersisted(
            riskScore: $row->risk_score,
            issuesDetected: $row->issues ?? [],
            recommendation: $row->recommendation,
            confidence: $row->confidence,
            citations: $row->citations ?? [],
            aiUsed: $row->ai_used,
            promptCode: $row->prompt_code,
            promptVersion: $row->prompt_version,
            modelCode: $row->model_code,
        );
    }

    /**
     * Only ever called with an $result where $result->available === true
     * (an "unavailable" AiPrecheckResult has nothing worth persisting as a
     * run — ComplianceAssistantService does not call save() in that case).
     */
    public function save(string $projectId, AiPrecheckResult $result): void
    {
        DB::transaction(function () use ($projectId, $result) {
            AiComplianceResultModel::where('project_id', $projectId)
                ->where('status', 'ACTIVE')
                ->update(['status' => 'SUPERSEDED']);

            AiComplianceResultModel::create([
                'project_id' => $projectId,
                'risk_score' => $result->riskScore,
                'issues' => $result->issuesDetected,
                'recommendation' => $result->recommendation?->value,
                'confidence' => $result->confidence,
                'citations' => $result->citations,
                'status' => 'ACTIVE',
                'ai_used' => $result->aiUsed,
                'prompt_code' => $result->promptCode,
                'prompt_version' => $result->promptVersion,
                'model_code' => $result->modelCode,
            ]);
        });
    }

    public function countActiveAboveRiskThreshold(int $threshold): int
    {
        return AiComplianceResultModel::where('status', 'ACTIVE')
            ->where('risk_score', '>', $threshold)
            ->count();
    }
}
