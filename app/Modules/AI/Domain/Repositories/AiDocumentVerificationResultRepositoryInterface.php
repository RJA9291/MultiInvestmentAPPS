<?php

namespace App\Modules\AI\Domain\Repositories;

use App\Modules\AI\Domain\ValueObjects\AiDocumentVerificationResult;

interface AiDocumentVerificationResultRepositoryInterface
{
    public function findLatestActiveForDocument(string $documentId): ?AiDocumentVerificationResult;

    /** Only ever called with an available result — see EloquentAiComplianceResultRepository's own note, mirrored here. */
    public function save(string $documentId, AiDocumentVerificationResult $result): void;

    /**
     * Added this Sprint for the Dashboard & Analytics Module's Compliance
     * Dashboard "Document Issues" KPI (proposed API-030). Counts ACTIVE
     * rows with a non-empty `issues` or `risk_flags` array.
     */
    public function countActiveWithIssues(): int;
}
