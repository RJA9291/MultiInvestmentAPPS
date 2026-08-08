<?php

namespace App\Modules\AI\Domain\ValueObjects;

/**
 * AiRecommendation (09_AI_ARCHITECTURE.md §20, Compliance Agent output)
 *
 * DELIBERATELY a distinct type from
 * App\Modules\Compliance\Domain\ValueObjects\ComplianceStatus. The two must
 * never be interchangeable — PDL-053 depends on it being structurally
 * impossible to pass an AI recommendation into a place that expects a real
 * compliance decision. ComplianceDecisionService::decide()'s $decision
 * parameter accepts a plain 'approved'/'rejected' string from a human-
 * submitted HTTP request, never an AiRecommendation value — there is no
 * conversion method between the two types anywhere in this codebase, and
 * there must never be one.
 */
enum AiRecommendation: string
{
    case Approve = 'APPROVE';
    case Reject = 'REJECT';
    case Review = 'REVIEW';
    case InsufficientData = 'INSUFFICIENT_DATA';
}
