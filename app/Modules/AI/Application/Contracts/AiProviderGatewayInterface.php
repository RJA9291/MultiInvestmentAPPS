<?php

namespace App\Modules\AI\Application\Contracts;

/**
 * AiProviderGatewayInterface — Layer 2 port (PDL-049, 14_LARAVEL_BLUEPRINT.md
 * §1.1 reconciliation 3). A concrete implementation belongs in
 * Modules/AI/Gateway (Layer 2, business orchestration: Provider Manager
 * AI-014, Model Router AI-015) and would itself call INTO
 * Core/Platform/AiGateway (Layer 1, infrastructure) — never the reverse.
 *
 * No concrete provider-calling implementation is bound in this Sprint 12
 * pass. AIServiceProvider binds this interface to NullAiProviderGateway,
 * which always signals "not configured" rather than fabricating a call to a
 * model that Registry-wise does not exist yet (every MODEL-XXX is "Not yet
 * selected", 09_AI_ARCHITECTURE.md §25). Swapping in a real implementation
 * once a Model reaches Active is a future step, not invented here.
 */
interface AiProviderGatewayInterface
{
    /**
     * @param  array<string, mixed>  $promptContext  e.g. ['prompt_code' => 'PROMPT-003',
     *     'prompt_version' => '1.0', 'project_data' => [...]]
     * @return array<string, mixed>  Raw response, shape validated by AiResponseValidator —
     *     this method itself does no validation.
     *
     * @throws \RuntimeException  if no provider is configured (NullAiProviderGateway's behavior).
     */
    public function analyze(array $promptContext): array;

    /** The MODEL-XXX code actually used, or null if none is configured. */
    public function modelCode(): ?string;
}
