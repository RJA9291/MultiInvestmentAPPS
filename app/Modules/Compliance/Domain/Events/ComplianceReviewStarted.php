<?php

namespace App\Modules\Compliance\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * ComplianceReviewStarted — fired the moment a new ComplianceReview cycle is
 * opened (ComplianceReviewService::openCycle()), independent of whether an
 * AI pre-check has run, is running, or has failed.
 *
 * This independence is deliberate and is the actual mechanism behind the
 * Project Owner's own Test Scenario 3 ("compliance review can start first,
 * AI result arrives later") and the "AI failure != system failure"
 * principle: there is no code path in this codebase where
 * AiCompliancePrecheckCompleted gates or delays this event. Both are
 * independent reactions to the same ProjectSubmitted/ProjectResubmitted
 * event (06_DOMAIN_MODEL.md §11's Context Map), not a sequential chain.
 *
 * Consumers (e.g. a future Notification Module's "AI Review Ready" /
 * "Compliance Review Assigned" alerts) may listen to this event and
 * separately to AiCompliancePrecheckCompleted without either one implying
 * anything about the other having happened yet.
 */
class ComplianceReviewStarted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $complianceReviewId,
        public readonly string $projectId,
    ) {
    }
}
