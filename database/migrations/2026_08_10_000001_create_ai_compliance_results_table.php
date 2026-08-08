<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DB-044: ai_compliance_results (AI Compliance Pre-check, §5 of the Project
 * Owner's AI-Compliance Integration brief)
 *
 * One row per pre-check RUN — mirrors the same append-only, never-deleted
 * pattern already established for `compliance_reviews` (PDL-027/PDL-028):
 * a new run never edits an old row, it inserts a new one and flips the
 * previous ACTIVE row to SUPERSEDED (EloquentAiComplianceResultRepository).
 * "Only latest ACTIVE result is used; old results kept for audit."
 *
 * CORRECTED from the Project Owner's proposed schema, applied directly
 * rather than re-asked (DBR-001 is unambiguous and already confirmed
 * without exception across all 43+ existing tables, including the Sprint
 * 11.5 registry tables): `$table->id()` (auto-increment) -> `$table->uuid('id')->primary()`.
 *
 * WAJIB: no soft/hard delete — same permanent-audit reasoning as
 * compliance_reviews, extended here because this table is direct input to
 * a human compliance decision and must remain fully reconstructable.
 *
 * `recommendation` values are validated at the application layer against
 * App\Modules\AI\Domain\ValueObjects\AiRecommendation (APPROVE, REJECT,
 * REVIEW, INSUFFICIENT_DATA) — not a DB-level ENUM, consistent with how
 * `status` columns elsewhere in this schema stay application-validated
 * strings rather than DB ENUMs (easier to extend without a migration).
 *
 * `ai_used`/`prompt_code`/`prompt_version`/`model_code` are the audit-log
 * fields the Project Owner's brief §8 asked for (ai_used, ai_prompt,
 * ai_model) — folded into this table rather than a separate audit table,
 * consistent with the canonical AI pipeline's "Store" step already being
 * folded into the Audit Logger (00_MASTER_PROMPT.md v3.11.0 changelog).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_compliance_results', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();

            $table->unsignedTinyInteger('risk_score'); // 0-100, app-validated (AiResponseValidator)
            $table->json('issues')->nullable();

            $table->string('recommendation'); // APPROVE | REJECT | REVIEW | INSUFFICIENT_DATA
            $table->unsignedTinyInteger('confidence'); // 0-100, app-validated

            $table->json('citations')->nullable();

            $table->string('status')->default('ACTIVE'); // ACTIVE | SUPERSEDED — never deleted

            $table->boolean('ai_used')->default(false);
            $table->string('prompt_code')->nullable(); // e.g. PROMPT-003
            $table->string('prompt_version')->nullable();
            $table->string('model_code')->nullable(); // e.g. MODEL-001, null until a Model is Active

            $table->timestamps();

            $table->index('project_id');
            $table->index('status');
            // Deliberately NO softDeletes() — permanent audit trail, see class doc comment above.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_compliance_results');
    }
};
