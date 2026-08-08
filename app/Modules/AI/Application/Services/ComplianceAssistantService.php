<?php

namespace App\Modules\AI\Application\Services;

use App\Modules\AI\Application\Contracts\AiProviderGatewayInterface;
use App\Modules\AI\Domain\Exceptions\InvalidAiPrecheckResponseException;
use App\Modules\AI\Domain\Repositories\AiComplianceResultRepositoryInterface;
use App\Modules\AI\Domain\ValueObjects\AiPrecheckResult;
use App\Modules\AI\Infrastructure\Registry\RegistryGate;
use Throwable;

/**
 * ComplianceAssistantService (09_AI_ARCHITECTURE.md §20, Compliance Agent /
 * AGENT-005; PROMPT-003) — orchestrates the full AI Compliance Pre-check
 * pipeline the Project Owner specified: RegistryGate -> AiProviderGateway ->
 * AiResponseValidator -> AiPrecheckResult -> AiComplianceResultRepository.
 *
 * ============================================================================
 * NON-NEGOTIABLE (09_AI_ARCHITECTURE.md §15 Human-in-the-Loop, §20; BR-139;
 * PDL-053): this class produces an AI PRE-CHECK ONLY. It has NO method, NO
 * dependency, and NO code path that writes to compliance_reviews.status. The
 * actual Approve/Reject decision is recorded exclusively by
 * App\Modules\Compliance\Application\Services\ComplianceDecisionService,
 * which is restricted to the Compliance Officer role. If a future change to
 * this file adds any call into ComplianceDecisionService, that change
 * violates this project's Golden Rule and must be rejected in review.
 * Note also App\Modules\AI\Domain\ValueObjects\AiRecommendation is a
 * deliberately distinct type from ComplianceStatus — there is no conversion
 * path between them anywhere in this codebase.
 * ============================================================================
 *
 * WAJIB (PDL-041, PDL-050): this Service checks the Registry (via
 * RegistryGate) for whether PROMPT-003 (Compliance) is Status = Active with
 * an assigned Owner BEFORE calling any provider. As of this build, PROMPT-003
 * is seeded as Draft (see database/seeders/RegistrySeeder.php, matching
 * 09_AI_ARCHITECTURE.md §21's own documented state) — so this Service
 * correctly, honestly refuses to run rather than fabricating an AI response
 * for a capability that isn't production-ready. This is also Scenario 3 of
 * the Project Owner's test scenarios ("AI fails -> system fallback to manual
 * review"): an inactive registry entry and a failed/invalid provider call
 * are handled identically — both degrade to an "unavailable" result, never
 * an exception that could block a human reviewer from proceeding.
 */
class ComplianceAssistantService
{
    private const PROMPT_CODE = 'PROMPT-003';

    public function __construct(
        private readonly RegistryGate $registryGate,
        private readonly AiProviderGatewayInterface $gateway,
        private readonly AiResponseValidator $validator,
        private readonly AiComplianceResultRepositoryInterface $results,
    ) {
    }

    /**
     * @param  array<int, string>  $documentTypesPresent  e.g. ['financial_statement', 'legal_opinion']
     */
    public function preCheck(string $projectId, array $documentTypesPresent): AiPrecheckResult
    {
        $gate = $this->registryGate->checkPrompt(self::PROMPT_CODE);

        if (! $gate['active']) {
            return AiPrecheckResult::unavailable($gate['reason']);
        }

        // Registry says Active — attempt a real provider call. As of this
        // build the bound implementation is NullAiProviderGateway (see
        // AIServiceProvider), which always throws, because no MODEL-XXX has
        // reached Active status either (09_AI_ARCHITECTURE.md §25). This
        // branch exists so that once BOTH the Prompt and a Model are
        // genuinely Active, wiring in a real AiProviderGatewayInterface
        // implementation is the only change needed here — nothing else in
        // this class, or in its callers, has to change.
        try {
            $promptVersion = $gate['prompt']?->version;

            $rawResponse = $this->gateway->analyze([
                'prompt_code' => self::PROMPT_CODE,
                'prompt_version' => $promptVersion,
                'project_id' => $projectId,
                'document_types_present' => $documentTypesPresent,
            ]);

            $validated = $this->validator->validate($rawResponse);

            $result = AiPrecheckResult::fromValidatedResponse(
                riskScore: $validated['risk_score'],
                issuesDetected: $validated['issues'],
                recommendation: $validated['recommendation'],
                confidence: $validated['confidence'],
                citations: $validated['citations'],
                promptCode: self::PROMPT_CODE,
                promptVersion: $promptVersion,
                modelCode: $this->gateway->modelCode(),
            );

            $this->results->save($projectId, $result);

            return $result;
        } catch (InvalidAiPrecheckResponseException|Throwable $e) {
            // Scenario 3: never let a provider failure or a malformed
            // response propagate as an exception that could block the
            // Compliance Officer's manual review. Nothing is persisted for
            // a failed run — the prior ACTIVE result (if any) remains the
            // latest one findLatestActiveForProject() will return.
            return AiPrecheckResult::unavailable(
                'AI pre-check failed and fell back to manual review: ' . $e->getMessage()
            );
        }
    }
}
