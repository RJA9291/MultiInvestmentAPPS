<?php

namespace App\Modules\AI\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * AiCompliancePrecheckCompleted — fired by RunAiComplianceJob once
 * ComplianceAssistantService::preCheck() returns, whether the result is a
 * real analysis or an "unavailable" fallback (Scenario 3 — this event fires
 * either way; consumers must check $result['available']).
 *
 * $result is AiPrecheckResult::toApiPayload()'s array shape (label,
 * available, reason, risk_score, issues_detected, recommendation,
 * confidence, citations, disclaimer) — a plain array, not the VO itself,
 * since Laravel queue/event serialization of a value object holding a
 * backed enum property is unnecessary indirection when the payload is
 * already a stable, documented array contract (API-012's own response shape).
 *
 * WAJIB: by the time this event fires, persistence has ALREADY happened
 * inside ComplianceAssistantService via AiComplianceResultRepositoryInterface
 * (when $result['available'] is true). Listeners of this event must never
 * insert into ai_compliance_results themselves — doing so would double-write
 * and bypass the repository's ACTIVE/SUPERSEDED bookkeeping and UUID
 * primary key generation.
 */
class AiCompliancePrecheckCompleted
{
    use Dispatchable, SerializesModels;

    /** @param  array<string, mixed>  $result */
    public function __construct(
        public readonly string $projectId,
        public readonly array $result,
    ) {
    }
}
