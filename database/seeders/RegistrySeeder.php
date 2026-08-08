<?php

namespace Database\Seeders;

use App\Modules\AI\Infrastructure\Registry\Eloquent\AiAgentModel;
use App\Modules\AI\Infrastructure\Registry\Eloquent\AiCapabilityModel;
use App\Modules\AI\Infrastructure\Registry\Eloquent\AiModelModel;
use App\Modules\AI\Infrastructure\Registry\Eloquent\AiPolicyModel;
use App\Modules\AI\Infrastructure\Registry\Eloquent\ApiRegistryModel;
use App\Modules\AI\Infrastructure\Registry\Eloquent\PromptModel;
use App\Modules\AI\Infrastructure\Registry\Eloquent\UiRegistryModel;
use Illuminate\Database\Seeder;

/**
 * Populates DB-037 through DB-043 (08_DATABASE_DESIGN.md §15, PDL-050) with
 * the EXACT data already locked in the markdown registries:
 *   - 09_AI_ARCHITECTURE.md §21 (Prompt Registry), §23 (AI Capability),
 *     §24 (AI Agent), §25 (AI Model), §26 (AI Policy)
 *   - 12_API_STANDARD.md §15 (API Registry)
 *   - 13_UI_ARCHITECTURE.md §9 (UI Registry)
 *
 * This is a data-fidelity seeder, not sample/fake data. Every row, status,
 * owner, and dependency list below is transcribed verbatim from the locked
 * documents. Where a document uses a placeholder/open value (e.g. Model
 * Registry's "Not yet selected" rows, or "*(open)*" cells), that placeholder
 * is preserved rather than invented.
 *
 * EXCEPTION (PDL-062, 2026-08-08): AGENT-005/PROMPT-003 (Compliance) and
 * AGENT-008/PROMPT-004 (Document) are seeded here as `Active` even though
 * 09_AI_ARCHITECTURE.md §21/§24's own markdown tables still show them as
 * `Draft` pending the §18 Evaluation Framework. This is a Project-Owner
 * explicit instruction to bypass that hard gate for MVP launch speed, not a
 * claim that the evaluation ran or passed — see each row's own `evaluation`
 * field for the exact wording, and PDL-062 in DRAFT_00_MASTER_PROMPT.md for
 * the authorizing decision. The markdown tables should be updated to match
 * (see 09_AI_ARCHITECTURE.md changelog); until then, this seeder is the
 * source of truth for what RegistryGate actually enforces at runtime, and
 * the markdown is intentionally flagged as lagging rather than silently
 * rewritten to look like it always said Active.
 *
 * Seed order matters: ai_capabilities/ai_agents/ai_models have no FK
 * dependencies and go first; prompts has real FKs to ai_agents/ai_models
 * (see 2026_08_08_000005_create_prompts_table.php doc comment) and must run
 * after both.
 */
class RegistrySeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAiCapabilities();
        $this->seedAiAgents();
        $this->seedAiModels();
        $this->seedPrompts();
        $this->seedAiPolicies();
        $this->seedApiRegistry();
        $this->seedUiRegistry();
    }

    private function seedAiCapabilities(): void
    {
        // 09_AI_ARCHITECTURE.md §23 — AI-008 intentionally absent (never allocated).
        $rows = [
            ['AI-001', 'Parser Service', 'Knowledge Context Team', 'Active', '1.0', [], 'N/A (deterministic parsing, not model-based)'],
            ['AI-002', 'OCR Service', 'Knowledge Context Team', 'Active', '1.0', ['AI-001'], 'N/A'],
            ['AI-003', 'Chunking Service', 'Knowledge Context Team', 'Active', '1.0', ['AI-001', 'AI-002'], 'N/A'],
            ['AI-004', 'Embedding Service', 'Knowledge Context Team', 'Active', '1.0', ['AI-003', 'AI-005'], 'N/A (measured indirectly via retrieval relevance, §18)'],
            ['AI-005', 'Vector Store', 'Knowledge Context Team (infrastructure-facing)', 'Active', '1.0', [], 'N/A'],
            ['AI-006', 'Retriever Service', 'Knowledge Context Team', 'Active', '1.0', ['AI-004', 'AI-005'], 'Yes — Grounding Accuracy, §18'],
            ['AI-007', 'Prompt Builder / Prompt Manager', 'AI Architecture Owner', 'Active', '1.0', ['Prompt Registry (§21)'], 'Yes — Evaluation Score per prompt version'],
            ['AI-009', 'Response Post-Processor / Guardrail Engine', 'AI Architecture Owner', 'Active', '1.0', ['AI-007', 'AI-011'], 'Yes — §18 gate is a precondition for any prompt reaching Active'],
            ['AI-010', 'Citation Engine', 'AI Architecture Owner', 'Active', '1.0', ['AI-006'], 'Yes — Citation Accuracy, §18'],
            ['AI-011', 'Confidence Scorer / Confidence Engine', 'AI Architecture Owner', 'Active', '1.0', ['AI-006', 'AI-010'], 'Yes — stability tracked across versions, §18'],
            ['AI-012', 'AI Capability Router', 'AI Architecture Owner', 'Active', '1.0', [], 'N/A (routing logic, not generative)'],
            ['AI-013', 'Context Builder', 'AI Architecture Owner', 'Active', '1.0', ['AI-006'], 'N/A (structural, deterministic isolation check)'],
            ['AI-014', 'Provider Manager', 'AI Architecture Owner (Layer 2, calls Layer 1 per ADR-010)', 'Active', '1.0', ['Integration Context adapters'], 'N/A'],
            ['AI-015', 'Model Router', 'AI Architecture Owner', 'Active', '1.0', ['AI-014', 'AI-016'], 'N/A'],
            ['AI-016', 'Cost Monitor', 'AI Architecture Owner (Layer 2) / Observability plumbing (Platform Services §12.1)', 'Active', '1.0', [], 'N/A (tracks Latency/Cost, §18, not itself evaluated for quality)'],
            ['AI-017', 'AI Agent Manager', 'AI Architecture Owner', 'Active', '1.0', ['AI Agent Registry, §24'], 'Indirect — each orchestrated capability evaluated individually'],
            ['AI-018', 'Response Validator', 'AI Architecture Owner (§17 Security Pipeline)', 'Draft', '1.0', ['AI-009'], 'Yes — Hallucination Detection, §18 (pending first evaluation run)'],
            ['AI-019', 'Response Formatter', 'AI Architecture Owner (§22 step 15)', 'Draft', '1.0', ['AI-009', 'AI-010', 'AI-011'], 'N/A (formatting only, no generative content)'],
        ];

        foreach ($rows as [$code, $name, $owner, $status, $version, $deps, $evaluation]) {
            AiCapabilityModel::updateOrCreate(
                ['code' => $code],
                [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'name' => $name,
                    'owner' => $owner,
                    'status' => $status,
                    'version' => $version,
                    'dependencies' => $deps,
                    'evaluation' => $evaluation,
                ]
            );
        }
    }

    private function seedAiAgents(): void
    {
        // 09_AI_ARCHITECTURE.md §24. Dependency lists kept as free-form
        // traceability strings, matching the markdown table's own mixed
        // AI-XXX / PROMPT-XXX / prose notation — not normalized here.
        $rows = [
            ['AGENT-001', 'Legal Agent', 'Legal Team', 'Active', '1.0', ['AI-006', 'AI-007', 'AI-009', 'AI-010', 'AI-011', 'PROMPT-001'], 'Enabled — PROMPT-001 passed §18 gate'],
            ['AGENT-002', 'Financial Agent', 'AI Architecture Owner', 'Draft', '1.0', ['AI-006', 'AI-007', 'AI-009', 'AI-010', 'AI-011', '(Prompt TBD)'], 'Not yet run'],
            ['AGENT-003', 'Investment Agent', 'AI Architecture Owner', 'Draft', '1.0', ['AI-006', 'AI-007', 'AI-009', 'AI-010', 'AI-011', 'AI-012', '(Prompt TBD)'], 'Not yet run'],
            ['AGENT-004', 'Risk Agent', 'AI Architecture Owner', 'Draft', '1.0', ['AI-006', 'AI-007', 'AI-009', 'AI-010', 'AI-011', 'PROMPT-002'], 'Pending §18 Golden Questions run'],
            ['AGENT-005', 'Compliance Agent', 'Compliance Team', 'Active', '1.0', ['AI-006', 'AI-007', 'AI-009', 'AI-010', 'AI-011', 'PROMPT-003'], 'Evaluation skipped — Project Owner explicit instruction, MVP launch speed, 2026-08-08, per PDL-062'],
            ['AGENT-006', 'Portfolio Agent (Composite, §20)', 'AI Architecture Owner', 'Draft', '1.0', ['AI-006', 'AI-007', 'AI-009', 'AI-010', 'AI-011', "Portfolio Context's PersonalPortfolioService (not AI-XXX)", 'PROMPT-005'], 'Pending — evaluated per underlying capability call, §21'],
            ['AGENT-007', 'Workspace Agent (Composite, §20)', 'AI Architecture Owner', 'Draft', '1.0', ['AI-006', 'AI-007', 'AI-009', 'AI-010', 'AI-011', 'Workspace/Notification Context services (not AI-XXX)', '(Prompt TBD)'], 'Not yet run'],
            ['AGENT-008', 'Document Agent', 'AI Architecture Owner', 'Active', '1.0', ['AI-006', 'AI-007', 'AI-009', 'AI-010', 'AI-011', 'PROMPT-004'], 'Evaluation skipped — Project Owner explicit instruction, MVP launch speed, 2026-08-08, per PDL-062'],
            ['AGENT-009', 'Meeting Agent (Composite, §20)', 'AI Architecture Owner', 'Draft', '1.0', ['AI-006', 'AI-007', 'AI-009', 'AI-010', 'AI-011', 'Workspace Context meeting data (not AI-XXX)', '(Prompt TBD)'], 'Not yet run'],
            ['AGENT-010', 'Reporting Agent', 'AI Architecture Owner', 'Draft', '1.0', ['AI-006', 'AI-007', 'AI-009', 'AI-010', 'AI-011', 'AI-017 (multi-agent internally)', '(Prompt TBD)'], 'Not yet run'],
        ];

        foreach ($rows as [$code, $name, $owner, $status, $version, $deps, $evaluation]) {
            AiAgentModel::updateOrCreate(
                ['code' => $code],
                [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'name' => $name,
                    'owner' => $owner,
                    'status' => $status,
                    'version' => $version,
                    'dependencies' => $deps,
                    'evaluation' => $evaluation,
                ]
            );
        }
    }

    private function seedAiModels(): void
    {
        // 09_AI_ARCHITECTURE.md §25 — all rows remain placeholders,
        // "Not yet selected" is the correct, locked status. Cost/latency/
        // context/capability columns are left null: the document itself
        // marks these cells "(open)"; inventing numbers here would violate
        // this project's "never assume, never invent" principle.
        $rows = [
            ['MODEL-001', '(placeholder — e.g., GPT-5.5)', 'AI Architecture Owner', 'Not yet selected', null, ['AI-014 (Provider Manager)']],
            ['MODEL-002', '(placeholder — e.g., Claude)', 'AI Architecture Owner', 'Not yet selected', null, ['AI-014']],
            ['MODEL-003', '(placeholder — e.g., Gemini)', 'AI Architecture Owner', 'Not yet selected', null, ['AI-014']],
            ['MODEL-004', '(placeholder — e.g., Llama, self-hosted)', 'AI Architecture Owner', 'Not yet selected', null, ['AI-014']],
        ];

        foreach ($rows as [$code, $name, $owner, $status, $version, $deps]) {
            AiModelModel::updateOrCreate(
                ['code' => $code],
                [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'name' => $name,
                    'owner' => $owner,
                    'status' => $status,
                    'version' => $version,
                    'dependencies' => $deps,
                    'cost_per_1k_tokens' => null,
                    'latency_ms' => null,
                    'max_context_tokens' => null,
                    'supports_vision' => null,
                    'supports_tools' => null,
                    'evaluation' => 'N/A until selected',
                ]
            );
        }
    }

    private function seedPrompts(): void
    {
        // 09_AI_ARCHITECTURE.md §21. agent_id/model_id are real FKs
        // (2026_08_08_000005_create_prompts_table.php) resolved here by
        // looking up the already-seeded AGENT-XXX code; model_id stays null
        // for every row because every MODEL-XXX is still "Not yet selected".
        $rows = [
            ['PROMPT-001', 'Legal', 'AGENT-001', 'Legal Team', '2.1', 'Active', 0.2, ['AI-006', 'AI-009', 'AI-010', 'AI-011'], '96%'],
            ['PROMPT-002', 'Risk', 'AGENT-004', 'AI Architecture Owner', '1.0', 'Draft', 0.2, ['AI-006', 'AI-009', 'AI-010', 'AI-011'], 'Pending §18 Golden Questions run'],
            ['PROMPT-003', 'Compliance', 'AGENT-005', 'Compliance Team', '1.0', 'Active', 0.1, ['AI-006', 'AI-009', 'AI-010', 'AI-011'], 'Evaluation skipped — Project Owner explicit instruction, MVP launch speed, 2026-08-08, per PDL-062'],
            ['PROMPT-004', 'Document', 'AGENT-008', 'AI Architecture Owner', '1.0', 'Active', 0.2, ['AI-006', 'AI-009', 'AI-010', 'AI-011'], 'Evaluation skipped — Project Owner explicit instruction, MVP launch speed, 2026-08-08, per PDL-062'],
            ['PROMPT-005', 'Portfolio', 'AGENT-006', 'AI Architecture Owner', '1.0', 'Draft', 0.3, ['AI-006', 'AI-009', 'AI-010', 'AI-011'], 'Pending — Composite Agent, evaluated per underlying capability call, not as one monolithic prompt'],
        ];

        foreach ($rows as [$code, $category, $agentCode, $owner, $version, $status, $temperature, $deps, $evaluation]) {
            $agent = AiAgentModel::where('code', $agentCode)->first();

            PromptModel::updateOrCreate(
                ['code' => $code],
                [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'name' => null, // flagged gap — see migration doc comment, source markdown has no Name column
                    'category' => $category,
                    'owner' => $owner,
                    'status' => $status,
                    'version' => $version,
                    'agent_id' => $agent?->id,
                    'model_id' => null, // no MODEL-XXX has reached a selected state yet
                    'temperature' => $temperature,
                    'dependencies' => $deps,
                    'evaluation' => $evaluation,
                ]
            );
        }
    }

    private function seedAiPolicies(): void
    {
        // 09_AI_ARCHITECTURE.md §26 — all 5 are Active/1.0. "dependencies"
        // here carries the "Actually enforced by" column verbatim, since
        // these Policies are catalog entries whose real enforcement lives
        // in the referenced BR/PDL/ADR/section, not in this table.
        $rows = [
            ['POL-AI-001', 'No Hallucination', ['§9 Guardrail Engine Golden Rule enforcement', '§18 Hallucination Detection (Evaluation Framework)', 'BR-071/BR-083 (NonAdvisoryOutputPolicy)']],
            ['POL-AI-002', 'Context Isolation', ['§6 Context Builder', 'ADR-005 (Portfolio/Investment isolation)', '§19 "No Cross Memory"']],
            ['POL-AI-003', 'Citation Required', ['§8 Citation Engine', 'BR-072 (MandatoryCitationOrNotFoundPolicy)']],
            ['POL-AI-004', 'Prompt Injection Protection', ['§17 AI Security Pipeline', 'PDL-029', 'PDL-030', "06_DOMAIN_MODEL.md v4.1.0's UntrustedContentPolicy/PromptInjectionGuardPolicy"]],
            ['POL-AI-005', 'Output Validation', ['§17 Response Validator (AI-018)', '§9 Guardrail Engine', '§22 pipeline steps 11-13']],
        ];

        foreach ($rows as [$code, $name, $deps]) {
            AiPolicyModel::updateOrCreate(
                ['code' => $code],
                [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'name' => $name,
                    'owner' => 'AI Architecture Owner',
                    'status' => 'Active',
                    'version' => '1.0',
                    'dependencies' => $deps,
                    'evaluation' => 'N/A (catalog entry — see referenced section for the actual enforcement mechanism)',
                ]
            );
        }
    }

    private function seedApiRegistry(): void
    {
        // 12_API_STANDARD.md §15 (v1.2.0). All rows remain Draft — no
        // endpoint has shipped yet. API-011/API-012 are now included: they
        // were formalized into the locked API Registry in v1.2.0 (Project
        // Owner review), so seeding them here now correctly reflects
        // governed state rather than an unapproved proposal.
        $rows = [
            ['API-001', 'POST', '/v1/projects', 'Investment Context Team', ['Project Aggregate (06_DOMAIN_MODEL.md §3)']],
            ['API-002', 'GET', '/v1/projects/{id}', 'Investment Context Team', ['Project Aggregate']],
            ['API-003', 'POST', '/v1/projects/{id}/submit', 'Investment Context Team', ['ProjectSubmitted (EVT)', 'Idempotency (§8)']],
            ['API-004', 'POST', '/v1/compliance-reviews/{id}/decide', 'Compliance Team', ['ComplianceReview Aggregate (§10)', 'ComplianceOfficerOnlyPolicy (BR-139)', 'Idempotency']],
            ['API-005', 'POST', '/v1/data-room-grants', 'Investment Context Team', ['DataRoomGrant Aggregate', 'Policy Engine (§4)']],
            ['API-006', 'GET', '/v1/projects/{id}/documents/{docId}', 'Investment Context Team', ['DataRoomGrant', 'API-AUTHZ-002']],
            ['API-007', 'POST', '/v1/ai/chat', 'AI Architecture Owner', ['Canonical AI Pipeline (09_AI_ARCHITECTURE.md §22)', 'AGENT-XXX (§20/§24)']],
            ['API-008', 'POST', '/v1/ai/jobs', 'AI Architecture Owner', ['AI Agent Manager (AI-017)', 'Async Job pattern (§10)']],
            ['API-009', 'GET', '/v1/ai/jobs/{id}', 'AI Architecture Owner', ['API-008']],
            ['API-010', 'POST', '/v1/documents', 'Knowledge Context Team', ['AI-001/AI-002/AI-003', 'PDL-029']],
            ['API-011', 'POST', '/v1/projects/{id}/publish', 'Investment Context Team', ['Project Aggregate', 'PublishEligibilityPolicy', 'ComplianceReviewGatePolicy (§10)']],
            ['API-012', 'POST', '/v1/projects/{id}/ai-precheck', 'AI Architecture Owner', ['ComplianceAssistantService', 'RegistryGate (PDL-041)', 'AGENT-005/PROMPT-003']],
        ];

        foreach ($rows as [$code, $method, $path, $owner, $deps]) {
            ApiRegistryModel::updateOrCreate(
                ['code' => $code],
                [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'name' => null, // method+path is the real identity, per migration doc comment
                    'method' => $method,
                    'path' => $path,
                    'owner' => $owner,
                    'status' => 'Draft',
                    'version' => '1.0',
                    'dependencies' => $deps,
                    'evaluation' => match ($code) {
                        'API-007', 'API-008' => 'Pending §18 Evaluation Framework, per-Agent',
                        'API-012' => 'Pending — inherits underlying Agent/Prompt evaluation, §18',
                        default => 'N/A',
                    },
                ]
            );
        }
    }

    private function seedUiRegistry(): void
    {
        // 13_UI_ARCHITECTURE.md §9. `description` is NOT NULL at the schema
        // level (PDL-046) — every row below supplies a real description
        // transcribed from the document, none left empty.
        $rows = [
            ['PAGE-001', 'Dashboard', 'page', 'Cross-context landing summary', ['Cross-context read projection'], [], 'Multiple (per-widget)'],
            ['PAGE-002', 'Project', 'page', "Single Project's full detail view", ['06_DOMAIN_MODEL.md §3 (Project Aggregate)'], ['API-001', 'API-002', 'API-003'], 'Active tab, submit-in-progress flag'],
            ['PAGE-003', 'Document', 'page', "Single Document's viewer", ['06_DOMAIN_MODEL.md §3 (Document Aggregate)'], ['API-006'], 'Viewer zoom/page position'],
            ['PAGE-004', 'Data Room', 'page', 'NDA-gated document access layer (Critical, §7)', ['DataRoomGrant Aggregate'], ['API-005', 'API-006'], 'Grant status, expiry countdown'],
            ['PAGE-005', 'Portfolio', 'page', 'Personal, platform-isolated investment tracking', ['Portfolio Context', 'ADR-005 isolation'], [], 'Personal filter/sort'],
            ['PAGE-006', 'AI Assistant', 'page', 'Chat/Insight Panel host page', ['09_AI_ARCHITECTURE.md §22'], ['API-007', 'API-008', 'API-009'], 'Conversation state (Module State, §5)'],
            ['PAGE-007', 'Compliance Review', 'page', "Compliance Officer's decision workflow", ['06_DOMAIN_MODEL.md §10', 'BR-139'], ['API-004'], 'Decision-in-progress flag'],
            ['COMPONENT-001', 'Table', 'component', 'Primary data-density primitive', ['12_API_STANDARD.md §6 (pagination contract)'], [], 'Pagination, Filter, Sort'],
            ['COMPONENT-002', 'Form', 'component', 'Structured input with schema validation', ['12_API_STANDARD.md API-SEC-002'], [], 'Field values, validation errors'],
            ['COMPONENT-003', 'Modal', 'component', 'Focused, blocking decision surface', [], [], 'Open/closed'],
            ['COMPONENT-004', 'Drawer', 'component', 'Supplementary, non-blocking detail surface', [], [], 'Open/closed'],
            ['COMPONENT-005', 'Notification', 'component', 'Renders Notification Aggregate', ['06_DOMAIN_MODEL.md §8 (Notification Aggregate)'], [], 'Unread count, feed pagination'],
            ['COMPONENT-006', 'AI Panel', 'component', 'Dedicated AI interaction surface, all 3 patterns (§8)', ['09_AI_ARCHITECTURE.md §8/§9/§10'], ['API-007', 'API-008', 'API-009'], 'Processing/Streaming/Partial/Final (UI-AI-006)'],
        ];

        foreach ($rows as [$code, $name, $type, $description, $deps, $apiDeps, $stateNotes]) {
            UiRegistryModel::updateOrCreate(
                ['code' => $code],
                [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'name' => $name,
                    'type' => $type,
                    'description' => $description,
                    'owner' => 'UI Architecture Owner',
                    'status' => 'Draft',
                    'version' => '1.0',
                    'dependencies' => $deps,
                    'api_dependencies' => $apiDeps,
                    'state_notes' => $stateNotes,
                    'evaluation' => $code === 'PAGE-006' ? 'Pending — inherits underlying Agent evaluation, §18' : 'N/A',
                ]
            );
        }
    }
}
