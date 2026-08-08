<?php

namespace App\Modules\AI\Infrastructure\Gateway;

use App\Modules\AI\Application\Contracts\AiProviderGatewayInterface;
use RuntimeException;

/**
 * NullAiProviderGateway — the safe default binding for
 * AiProviderGatewayInterface (bound in AIServiceProvider) until a real
 * Provider Manager (AI-014) / Model Router (AI-015) integration exists and
 * an AI Model Registry entry reaches Active (09_AI_ARCHITECTURE.md §25 —
 * every MODEL-XXX is currently "Not yet selected").
 *
 * In practice, ComplianceAssistantService's RegistryGate check already
 * refuses to reach this class (PROMPT-003 is Draft), so this class's
 * analyze() method is not expected to be called in this build at all. It
 * exists so the Application layer has a real, always-resolvable binding
 * to depend on — never a nullable constructor param the container can't
 * satisfy — while remaining honest that no provider is actually wired up.
 */
class NullAiProviderGateway implements AiProviderGatewayInterface
{
    public function analyze(array $promptContext): array
    {
        throw new RuntimeException(
            'No AI provider is configured (NullAiProviderGateway). This should be unreachable while '
                . 'RegistryGate correctly reports PROMPT-003/every MODEL-XXX as not-Active — if this '
                . 'exception fires, the registry gate check was bypassed somewhere, which is a bug.'
        );
    }

    public function modelCode(): ?string
    {
        return null;
    }
}
