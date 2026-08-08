<?php

namespace App\Modules\AI\Infrastructure\Repositories;

use App\Modules\AI\Domain\Repositories\AiDocumentVerificationResultRepositoryInterface;
use App\Modules\AI\Domain\ValueObjects\AiDocumentVerificationResult;
use App\Modules\AI\Infrastructure\Eloquent\AiDocumentVerificationResultModel;
use Illuminate\Support\Facades\DB;

/** Mirrors EloquentAiComplianceResultRepository's ACTIVE/SUPERSEDED bookkeeping, scoped per document_id instead of project_id. */
class EloquentAiDocumentVerificationResultRepository implements AiDocumentVerificationResultRepositoryInterface
{
    public function findLatestActiveForDocument(string $documentId): ?AiDocumentVerificationResult
    {
        $row = AiDocumentVerificationResultModel::where('document_id', $documentId)
            ->where('status', 'ACTIVE')
            ->latest('created_at')
            ->first();

        if (! $row) {
            return null;
        }

        return AiDocumentVerificationResult::fromPersisted(
            completenessScore: $row->completeness_score,
            issues: $row->issues ?? [],
            riskFlags: $row->risk_flags ?? [],
            recommendation: $row->recommendation,
            confidence: $row->confidence,
            citations: $row->citations ?? [],
            aiUsed: $row->ai_used,
            promptCode: $row->prompt_code,
            promptVersion: $row->prompt_version,
            modelCode: $row->model_code,
        );
    }

    public function save(string $documentId, AiDocumentVerificationResult $result): void
    {
        DB::transaction(function () use ($documentId, $result) {
            AiDocumentVerificationResultModel::where('document_id', $documentId)
                ->where('status', 'ACTIVE')
                ->update(['status' => 'SUPERSEDED']);

            AiDocumentVerificationResultModel::create([
                'document_id' => $documentId,
                'completeness_score' => $result->completenessScore,
                'issues' => $result->issues,
                'risk_flags' => $result->riskFlags,
                'recommendation' => $result->recommendation,
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

    public function countActiveWithIssues(): int
    {
        return AiDocumentVerificationResultModel::where('status', 'ACTIVE')
            ->where(function ($query) {
                $query->whereJsonLength('issues', '>', 0)
                    ->orWhereJsonLength('risk_flags', '>', 0);
            })
            ->count();
    }
}
