<?php

namespace Tests\Unit\Modules\AI;

use App\Modules\AI\Application\Contracts\AiProviderGatewayInterface;
use App\Modules\AI\Application\Services\AiResponseValidator;
use App\Modules\AI\Application\Services\ComplianceAssistantService;
use App\Modules\AI\Domain\Repositories\AiComplianceResultRepositoryInterface;
use App\Modules\AI\Infrastructure\Registry\RegistryGate;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * NOT YET EXECUTED — see AiResponseValidatorTest's doc comment; same
 * sandbox limitation applies.
 *
 * Scenario 3 (Project Owner's brief §10): "AI fails -> system fallback to
 * manual review." Covers both ways AI can "fail" in this codebase: the
 * Registry gate reporting not-Active (the current, real state — PROMPT-003
 * is Draft), and a provider throwing (the future state once a Model is
 * Active but the call itself errors). Both must degrade to an "unavailable"
 * AiPrecheckResult and MUST NOT throw past this Service — a Compliance
 * Officer's manual review must never be blocked by an AI failure.
 */
class ComplianceAssistantServiceFallbackTest extends TestCase
{
    public function test_it_returns_unavailable_when_registry_gate_is_not_active(): void
    {
        $gate = $this->createMock(RegistryGate::class);
        $gate->method('checkPrompt')->with('PROMPT-003')->willReturn([
            'active' => false,
            'prompt' => null,
            'reason' => 'PROMPT-003 has Status = Draft, not Active (PDL-041).',
        ]);

        $gateway = $this->createMock(AiProviderGatewayInterface::class);
        $gateway->expects($this->never())->method('analyze'); // never reached — this IS the point

        $results = $this->createMock(AiComplianceResultRepositoryInterface::class);
        $results->expects($this->never())->method('save'); // nothing to persist for an unavailable run

        $service = new ComplianceAssistantService($gate, $gateway, new AiResponseValidator(), $results);

        $result = $service->preCheck('project-1', []);

        $this->assertFalse($result->available);
        $this->assertStringContainsString('PDL-041', $result->reason);
        $payload = $result->toApiPayload();
        $this->assertSame('AI Recommendation', $payload['label']); // PDL-058 — present even when unavailable
        $this->assertNull($payload['risk_score']);
    }

    public function test_it_falls_back_safely_when_the_provider_throws(): void
    {
        $gate = $this->createMock(RegistryGate::class);
        $gate->method('checkPrompt')->willReturn([
            'active' => true,
            'prompt' => (object) ['version' => '1.0'],
            'reason' => null,
        ]);

        $gateway = $this->createMock(AiProviderGatewayInterface::class);
        $gateway->method('analyze')->willThrowException(new RuntimeException('Provider timeout'));

        $results = $this->createMock(AiComplianceResultRepositoryInterface::class);
        $results->expects($this->never())->method('save'); // a failed run is never persisted

        $service = new ComplianceAssistantService($gate, $gateway, new AiResponseValidator(), $results);

        $result = $service->preCheck('project-2', []);

        $this->assertFalse($result->available);
        $this->assertStringContainsString('fell back to manual review', $result->reason);
    }

    public function test_it_falls_back_safely_when_the_provider_response_is_malformed(): void
    {
        $gate = $this->createMock(RegistryGate::class);
        $gate->method('checkPrompt')->willReturn([
            'active' => true,
            'prompt' => (object) ['version' => '1.0'],
            'reason' => null,
        ]);

        $gateway = $this->createMock(AiProviderGatewayInterface::class);
        $gateway->method('analyze')->willReturn(['risk_score' => 999]); // out of range, missing fields

        $results = $this->createMock(AiComplianceResultRepositoryInterface::class);
        $results->expects($this->never())->method('save');

        $service = new ComplianceAssistantService($gate, $gateway, new AiResponseValidator(), $results);

        $result = $service->preCheck('project-3', []);

        $this->assertFalse($result->available);
    }
}
