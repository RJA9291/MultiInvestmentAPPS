<?php

namespace App\Modules\AI\Application\Listeners;

use App\Modules\AI\Domain\Events\AiCompliancePrecheckCompleted;
use Illuminate\Support\Facades\Log;

/**
 * HandleAiPrecheckCompleted — reactive side effects ONLY.
 *
 * Deliberately does NOT persist to `ai_compliance_results` (unlike the
 * Project Owner's original sketch, which inserted via a raw DB::table()
 * call here). Persistence already happened, if the run was available,
 * inside ComplianceAssistantService via AiComplianceResultRepositoryInterface
 * — that repository is the only code path allowed to write this table
 * (it owns UUID generation and the ACTIVE/SUPERSEDED bookkeeping). Doing it
 * again here would double-write and risk a second row with mismatched
 * status bookkeeping.
 *
 * Also deliberately does NOT dispatch ComplianceReviewStarted — seer
 * App\Modules\Compliance\Domain\Events\ComplianceReviewStarted's own doc
 * comment for why that event is intentionally independent of this one.
 *
 * High-risk flagging and Compliance Officer notification (Project Owner's
 * brief §8, both marked "OPTIONAL / ADVANCED") are represented here as a
 * structured log entry only — a real Notification Module does not exist
 * yet (it is literally one of the Project Owner's own offered next steps).
 * Wiring a real notification is a future change to this one method, not
 * invented here.
 */
class HandleAiPrecheckCompleted
{
    private const HIGH_RISK_THRESHOLD = 80;

    public function handle(AiCompliancePrecheckCompleted $event): void
    {
        if (! ($event->result['available'] ?? false)) {
            return;
        }

        $riskScore = $event->result['risk_score'] ?? null;

        if (is_int($riskScore) && $riskScore > self::HIGH_RISK_THRESHOLD) {
            Log::warning('AI compliance pre-check flagged a high-risk project.', [
                'project_id' => $event->projectId,
                'risk_score' => $riskScore,
                'recommendation' => $event->result['recommendation'] ?? null,
                // FLAGGED: no Notification Module exists yet to alert the
                // Compliance Officer in real time — this log entry is the
                // honest current behavior, not a stand-in for a fake send.
            ]);
        }
    }
}
