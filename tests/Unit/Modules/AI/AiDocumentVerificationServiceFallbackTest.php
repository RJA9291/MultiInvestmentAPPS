<?php

namespace Tests\Unit\Modules\AI;

use App\Modules\AI\Application\Contracts\AiProviderGatewayInterface;
use App\Modules\AI\Application\Contracts\DocumentContentExtractorInterface;
use App\Modules\AI\Application\Services\AiDocumentVerificationResponseValidator;
use App\Modules\AI\Application\Services\AiDocumentVerificationService;
use App\Modules\AI\Domain\Repositories\AiDocumentVerificationResultRepositoryInterface;
use App\Modules\AI\Infrastructure\Registry\RegistryGate;
use App\Modules\Document\Domain\Entities\Document;
use App\Modules\Document\Domain\Repositories\DocumentRepositoryInterface;
use App\Modules\Document\Domain\ValueObjects\Attachment;
use App\Modules\Document\Infrastructure\Storage\FileStorageGatewayInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * NOT YET EXECUTED — see AiResponseValidatorTest's doc comment; same
 * sandbox limitation applies.
 *
 * Project Owner's brief §13 Scenario 3: "AI fails -> system still works."
 * Covers every failure mode this Service can hit: Registry gate not-Active
 * (the real current state — PROMPT-004 is Draft), the Document itself
 * missing, a provider throwing, and a malformed provider response. All four
 * MUST degrade to an "unavailable" AiDocumentVerificationResult and MUST
 * NOT throw past this Service, and MUST NOT persist anything.
 */
class AiDocumentVerificationServiceFallbackTest extends TestCase
{
    private function makeService(
        RegistryGate $gate,
        AiProviderGatewayInterface $gateway,
        AiDocumentVerificationResultRepositoryInterface $results,
        DocumentRepositoryInterface $documents,
        ?FileStorageGatewayInterface $storage = null,
        ?DocumentContentExtractorInterface $extractor = null,
    ): AiDocumentVerificationService {
        return new AiDocumentVerificationService(
            $gate,
            $gateway,
            new AiDocumentVerificationResponseValidator(),
            $results,
            $documents,
            $storage ?? $this->createMock(FileStorageGatewayInterface::class),
            $extractor ?? $this->createMock(DocumentContentExtractorInterface::class),
        );
    }

    public function test_it_returns_unavailable_when_registry_gate_is_not_active(): void
    {
        $gate = $this->createMock(RegistryGate::class);
        $gate->method('checkPrompt')->with('PROMPT-004')->willReturn([
            'active' => false,
            'prompt' => null,
            'reason' => 'PROMPT-004 has Status = Draft, not Active (PDL-041).',
        ]);

        $gateway = $this->createMock(AiProviderGatewayInterface::class);
        $gateway->expects($this->never())->method('analyze');

        $results = $this->createMock(AiDocumentVerificationResultRepositoryInterface::class);
        $results->expects($this->never())->method('save');

        $documents = $this->createMock(DocumentRepositoryInterface::class);
        $documents->expects($this->never())->method('find'); // gate is checked first

        $result = $this->makeService($gate, $gateway, $results, $documents)->verify('doc-1', 'project-1');

        $this->assertFalse($result->available);
        $this->assertStringContainsString('PDL-041', $result->reason);
        $payload = $result->toApiPayload();
        $this->assertSame('AI Recommendation', $payload['label']);
        $this->assertNull($payload['completeness_score']);
    }

    public function test_it_returns_unavailable_when_the_document_is_not_found(): void
    {
        $gate = $this->createMock(RegistryGate::class);
        $gate->method('checkPrompt')->willReturn([
            'active' => true,
            'prompt' => (object) ['version' => '1.0'],
            'reason' => null,
        ]);

        $gateway = $this->createMock(AiProviderGatewayInterface::class);
        $gateway->expects($this->never())->method('analyze');

        $results = $this->createMock(AiDocumentVerificationResultRepositoryInterface::class);
        $results->expects($this->never())->method('save');

        $documents = $this->createMock(DocumentRepositoryInterface::class);
        $documents->method('find')->with('missing-doc')->willReturn(null);

        $result = $this->makeService($gate, $gateway, $results, $documents)->verify('missing-doc', 'project-1');

        $this->assertFalse($result->available);
        $this->assertStringContainsString('not found', $result->reason);
    }

    public function test_it_falls_back_safely_when_the_provider_throws(): void
    {
        $gate = $this->createMock(RegistryGate::class);
        $gate->method('checkPrompt')->willReturn([
            'active' => true,
            'prompt' => (object) ['version' => '1.0'],
            'reason' => null,
        ]);

        $attachment = new Attachment('file-1', 'report.pdf', 'application/pdf', 'documents/p1/d1/file-1_report.pdf');
        $document = $this->createMock(Document::class);
        $document->method('currentAttachment')->willReturn($attachment);
        $document->method('documentType')->willReturn('financial_statement');

        $documents = $this->createMock(DocumentRepositoryInterface::class);
        $documents->method('find')->willReturn($document);

        $storage = $this->createMock(FileStorageGatewayInterface::class);
        $storage->method('retrieve')->willReturn('%PDF-1.4 raw bytes');

        $extractor = $this->createMock(DocumentContentExtractorInterface::class);
        $extractor->method('extract')->willReturn(''); // PDF not supported by PlainTextPassthroughExtractor

        $gateway = $this->createMock(AiProviderGatewayInterface::class);
        $gateway->method('analyze')->willThrowException(new RuntimeException('Provider timeout'));

        $results = $this->createMock(AiDocumentVerificationResultRepositoryInterface::class);
        $results->expects($this->never())->method('save');

        $result = $this->makeService($gate, $gateway, $results, $documents, $storage, $extractor)
            ->verify('doc-1', 'project-1');

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

        $attachment = new Attachment('file-2', 'report.txt', 'text/plain', 'documents/p1/d2/file-2_report.txt');
        $document = $this->createMock(Document::class);
        $document->method('currentAttachment')->willReturn($attachment);
        $document->method('documentType')->willReturn('financial_statement');

        $documents = $this->createMock(DocumentRepositoryInterface::class);
        $documents->method('find')->willReturn($document);

        $storage = $this->createMock(FileStorageGatewayInterface::class);
        $storage->method('retrieve')->willReturn('plain text content');

        $extractor = $this->createMock(DocumentContentExtractorInterface::class);
        $extractor->method('extract')->willReturn('plain text content');

        $gateway = $this->createMock(AiProviderGatewayInterface::class);
        $gateway->method('analyze')->willReturn(['completeness_score' => 999]); // out of range, missing fields

        $results = $this->createMock(AiDocumentVerificationResultRepositoryInterface::class);
        $results->expects($this->never())->method('save');

        $result = $this->makeService($gate, $gateway, $results, $documents, $storage, $extractor)
            ->verify('doc-2', 'project-1');

        $this->assertFalse($result->available);
    }
}
