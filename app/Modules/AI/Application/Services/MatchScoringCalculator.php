<?php

namespace App\Modules\AI\Application\Services;

/**
 * MatchScoringCalculator — pure, deterministic, no I/O, no AI provider call.
 *
 * RECONCILIATION, stated up front: the Project Owner's brief titled this
 * whole capability "AI Investor-Project Matching Engine" and gave a
 * 4-factor weighted formula (industry_match 30%, budget_fit 25%,
 * risk_alignment 25%, activity_interest 20%). Two corrections were
 * necessary before any code could be written:
 *
 * 1. **`budget_fit` is NOT implemented.** It requires comparing the
 *    investor's `investment_range` against a Project "funding required"
 *    amount — but `06_DOMAIN_MODEL.md` §3.1's `NoFinancialFieldPolicy`
 *    (BR-020, BR-028) structurally forbids any financial/monetary field on
 *    the `Project` Aggregate ("a project record must never contain a
 *    financial-transaction field"). Inventing a `funding_required` column
 *    to satisfy this one factor would violate an already-locked Business
 *    Rule. The remaining three factors are renormalized to sum to 100%
 *    (industry_match 40%, risk_alignment ~33.3%, activity_interest ~26.7%
 *    — same relative proportions as the original 30:25:20 weights). This
 *    is a real, tracked gap (`04_BUSINESS_RULES.md` BR-151) — closing it
 *    properly would require a Project Owner decision on how a funding
 *    target could be modeled without violating BR-020/PDL-009, not a
 *    silent workaround here.
 *
 * 2. This class produces NO generative text and calls no AI provider — it
 *    is plain weighted-average arithmetic over structured fields, exactly
 *    the kind of deterministic business-rule scoring already used
 *    elsewhere in this codebase (e.g. `PublishEligibilityPolicy`). It is
 *    named `MatchScoringCalculator`, not `AiMatchingCalculator`, precisely
 *    so nobody mistakes rule-based arithmetic for a language-model output —
 *    the "AI" branding in the Project Owner's brief refers to the platform
 *    capability as a whole (`AiInvestorMatchingService` orchestrates it),
 *    not to this calculator specifically.
 */
class MatchScoringCalculator
{
    private const WEIGHT_INDUSTRY = 0.4;

    private const WEIGHT_RISK = 1 / 3;

    private const WEIGHT_ACTIVITY = 1 - self::WEIGHT_INDUSTRY - (1 / 3);

    private const RISK_BANDS = [
        'LOW' => [0, 33],
        'MEDIUM' => [34, 66],
        'HIGH' => [67, 100],
    ];

    /**
     * @return array{score: int, breakdown: array<string, int>, reasons: array<int, string>, confidence: float}
     */
    public function calculate(
        ?string $investorPreferredIndustry,
        ?string $investorRiskAppetite,
        ?string $projectCategory,
        ?int $projectRiskScore,
        bool $investorHasExistingAccessRequest,
    ): array {
        $reasons = [];
        $usableFactors = 0;

        // --- Industry match ---
        if ($investorPreferredIndustry === null || $projectCategory === null) {
            $industryScore = 0;
            $reasons[] = $investorPreferredIndustry === null
                ? 'Investor has not set a preferred industry — industry criterion not evaluated.'
                : 'This project has no category set — industry criterion not evaluated.';
        } else {
            $isMatch = mb_strtolower(trim($investorPreferredIndustry)) === mb_strtolower(trim($projectCategory));
            $industryScore = $isMatch ? 100 : 0;
            $reasons[] = $isMatch
                ? "Industry preference ({$investorPreferredIndustry}) matches this project's category."
                : "Industry preference ({$investorPreferredIndustry}) does not match this project's category ({$projectCategory}).";
            $usableFactors++;
        }

        // --- Risk alignment ---
        $investorBand = $investorRiskAppetite !== null ? mb_strtoupper(trim($investorRiskAppetite)) : null;
        if ($investorBand === null || ! isset(self::RISK_BANDS[$investorBand]) || $projectRiskScore === null) {
            $riskScore = 0;
            $reasons[] = $projectRiskScore === null
                ? 'This project has no AI compliance risk score yet — risk criterion not evaluated.'
                : 'Investor has not set a recognized risk appetite (Low/Medium/High) — risk criterion not evaluated.';
        } else {
            $projectBand = $this->bandForScore($projectRiskScore);
            $riskScore = $this->riskAlignmentScore($investorBand, $projectBand);
            $reasons[] = $riskScore === 100
                ? "Investor's risk appetite ({$investorBand}) aligns with this project's AI-assessed risk band ({$projectBand})."
                : "Investor's risk appetite ({$investorBand}) partially aligns with this project's AI-assessed risk band ({$projectBand}).";
            $usableFactors++;
        }

        // --- Activity / prior engagement ---
        $activityScore = $investorHasExistingAccessRequest ? 100 : 0;
        $reasons[] = $investorHasExistingAccessRequest
            ? 'Investor has previously requested access to this project.'
            : 'No prior access request from this investor for this project.';
        $usableFactors++; // always computable — absence of a request is itself a real, non-null signal

        $total = (int) round(
            ($industryScore * self::WEIGHT_INDUSTRY)
            + ($riskScore * self::WEIGHT_RISK)
            + ($activityScore * self::WEIGHT_ACTIVITY)
        );

        return [
            'score' => max(0, min(100, $total)),
            'breakdown' => [
                'industry_match' => $industryScore,
                'risk_alignment' => $riskScore,
                'activity_interest' => $activityScore,
            ],
            'reasons' => $reasons,
            // 3 possible usable factors (industry, risk, activity); activity is
            // always usable, so confidence ranges from 0.33 (only activity
            // computable) to 1.0 (all three computable) — an honest measure of
            // how much real profile data backed this score, not a probability
            // of deal success.
            'confidence' => round($usableFactors / 3, 2),
        ];
    }

    private function bandForScore(int $score): string
    {
        return match (true) {
            $score >= 67 => 'HIGH',
            $score >= 34 => 'MEDIUM',
            default => 'LOW',
        };
    }

    private function riskAlignmentScore(string $investorBand, string $projectBand): int
    {
        if ($investorBand === $projectBand) {
            return 100;
        }

        $order = ['LOW' => 0, 'MEDIUM' => 1, 'HIGH' => 2];
        $distance = abs($order[$investorBand] - $order[$projectBand]);

        return $distance === 1 ? 50 : 0;
    }
}
