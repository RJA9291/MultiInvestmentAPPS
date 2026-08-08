<?php

namespace App\Modules\AI\Infrastructure\Gateway;

use App\Modules\AI\Application\Contracts\AiProviderGatewayInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * OpenAiProviderGateway — a real AiProviderGatewayInterface implementation
 * (Provider Manager, AI-014). Bound in AIServiceProvider only when
 * `config('services.openai.api_key')` is set; falls back to
 * NullAiProviderGateway otherwise, so a missing key degrades safely rather
 * than throwing at boot.
 *
 * ============================================================================
 * STATUS (2026-08-08, PDL-062): both RegistryGate call sites
 * (ComplianceAssistantService's PROMPT-003, AiDocumentVerificationService's
 * PROMPT-004) now DO reach this class — RegistrySeeder.php marks both
 * `Active`. This is NOT because either Prompt passed §18's Evaluation
 * Framework (Grounding Accuracy, Citation Accuracy, Hallucination Detection,
 * both directions of Golden Questions — the normal, real path to `Active`).
 * It is a narrow, explicitly logged Project Owner exception (PDL-062,
 * `00_MASTER_PROMPT.md`) to skip that gate for MVP launch speed. Each row's
 * `evaluation` field in RegistrySeeder.php says so honestly ("Evaluation
 * skipped — Project Owner explicit instruction..."), never a fabricated
 * passing score. A real §18 run against real sample data, reviewed by each
 * Prompt's assigned Owner (Compliance Team / AI Architecture Owner), remains
 * open and tracked — this class's actual output quality has not been
 * evaluated.
 * ============================================================================
 *
 * `modelCode()` deliberately does NOT return a `MODEL-XXX` registry code —
 * every row in the AI Model Registry (`ai_models`, DB-040) is still a named
 * placeholder ("Not yet selected", `09_AI_ARCHITECTURE.md` §25); claiming
 * one is "actually used" would misrepresent registry state. It returns the
 * literal provider model string instead (`config('services.openai.model')`),
 * which is the honest, verifiable fact of what was called.
 */
class OpenAiProviderGateway implements AiProviderGatewayInterface
{
    private const ENDPOINT = 'https://api.openai.com/v1/chat/completions';

    public function analyze(array $promptContext): array
    {
        $apiKey = config('services.openai.api_key');

        if (empty($apiKey)) {
            throw new RuntimeException('OpenAiProviderGateway called with no OPENAI_API_KEY configured.');
        }

        [$system, $user] = match ($promptContext['prompt_code'] ?? null) {
            'PROMPT-003' => [$this->compliancePreCheckSystemPrompt(), $this->compliancePreCheckUserPrompt($promptContext)],
            'PROMPT-004' => [$this->documentVerificationSystemPrompt(), $this->documentVerificationUserPrompt($promptContext)],
            default => throw new RuntimeException(
                "OpenAiProviderGateway has no prompt template for prompt_code '" . ($promptContext['prompt_code'] ?? 'null') . "'."
            ),
        };

        $response = Http::withToken($apiKey)
            ->timeout((int) config('services.openai.timeout_seconds', 30))
            ->post(self::ENDPOINT, [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $user],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.1,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('OpenAI API request failed: HTTP ' . $response->status() . ' — ' . $response->body());
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content)) {
            throw new RuntimeException('OpenAI API response missing choices[0].message.content.');
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('OpenAI API response content was not valid JSON: ' . $content);
        }

        // AiResponseValidator/AiDocumentVerificationResponseValidator require
        // real int/float types, not numeric strings — json_decode already
        // gives us that for a well-formed JSON number, but a model can still
        // emit "85" as a JSON string; coerce defensively rather than let a
        // type mismatch masquerade as "the AI is broken."
        foreach (['risk_score', 'completeness_score'] as $intField) {
            if (isset($decoded[$intField]) && is_numeric($decoded[$intField])) {
                $decoded[$intField] = (int) $decoded[$intField];
            }
        }

        if (isset($decoded['confidence']) && is_numeric($decoded['confidence'])) {
            // PROMPT-003 (compliance) wants an int 0-100; PROMPT-004 (document
            // verification) wants a float 0.0-1.0 — the two validators enforce
            // this exact distinction, so the cast must match prompt_code.
            $decoded['confidence'] = ($promptContext['prompt_code'] ?? null) === 'PROMPT-003'
                ? (int) $decoded['confidence']
                : (float) $decoded['confidence'];
        }

        return $decoded;
    }

    public function modelCode(): ?string
    {
        return config('services.openai.model');
    }

    private function compliancePreCheckSystemPrompt(): string
    {
        return <<<'PROMPT'
            You are an advisory-only compliance pre-check assistant for an investment
            documentation platform. You NEVER approve, reject, or issue an investment
            recommendation — a human Compliance Officer makes every real decision.
            Analyze the project's declared document types for completeness gaps and
            obvious red flags only. Respond with ONLY a JSON object, no prose, matching
            exactly this shape:
            {"risk_score": <integer 0-100>, "confidence": <integer 0-100>,
             "recommendation": "APPROVE"|"REJECT"|"REVIEW"|"INSUFFICIENT_DATA",
             "issues": [<string>, ...], "citations": [<string>, ...]}
            "recommendation" here means "how confident is a pre-check, not a decision" —
            never phrase issues as an investment verdict.
            PROMPT;
    }

    private function compliancePreCheckUserPrompt(array $ctx): string
    {
        $types = implode(', ', $ctx['document_types_present'] ?? []);

        return "Project ID: {$ctx['project_id']}\nDocument types present: {$types}\n"
            . 'Evaluate completeness and obvious risk flags only, per the schema given.';
    }

    private function documentVerificationSystemPrompt(): string
    {
        return <<<'PROMPT'
            You are an advisory-only document verification assistant for an investment
            documentation platform. You NEVER approve, reject, or alter a document's
            status — a human reviewer makes every real decision. Respond with ONLY a
            JSON object, no prose, matching exactly this shape:
            {"completeness_score": <integer 0-100>, "confidence": <float 0.0-1.0>,
             "recommendation": "<short free-text guidance for the human reviewer>",
             "issues": [<string>, ...], "risk_flags": [<string>, ...], "citations": [<string>, ...]}
            PROMPT;
    }

    private function documentVerificationUserPrompt(array $ctx): string
    {
        $content = mb_substr((string) ($ctx['content'] ?? ''), 0, 8000);

        return "Document type: {$ctx['document_type']}\nFile name: {$ctx['file_name']}\n"
            . "Mime type: {$ctx['mime_type']}\n\nContent:\n{$content}";
    }
}
