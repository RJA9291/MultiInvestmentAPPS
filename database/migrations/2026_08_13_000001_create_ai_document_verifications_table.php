<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DB-045: ai_document_verifications (AI Document Verification Module, per
 * the Project Owner's brief) — one row per verification RUN, mirroring
 * `ai_compliance_results`' (DB-044) append-only ACTIVE/SUPERSEDED pattern
 * exactly, scoped per `document_id` instead of `project_id`.
 *
 * CORRECTED from the Project Owner's proposed schema, applied directly
 * rather than re-asked (DBR-001 confirmed without exception across all
 * prior tables, including DB-044): `$table->id()` (auto-increment) ->
 * `$table->uuid('id')->primary()`; `document_id` typed as a real
 * `foreignUuid` FK to `documents` (DB-006), not a bare `string`.
 *
 * `recommendation` is stored as free TEXT here, deliberately NOT the
 * `AiRecommendation` enum `ai_compliance_results.recommendation` uses —
 * see AiDocumentVerificationResult's doc comment for why these two
 * "recommendation" concepts must stay structurally distinct (PDL-053).
 *
 * `confidence` is `decimal(3,2)` (0.00-1.00), matching the Project Owner's
 * own 0.91 example — a different scale from `ai_compliance_results.confidence`
 * (0-100 integer). This is an intentionally separate, isolated contract.
 *
 * WAJIB: no soft/hard delete — same permanent-audit reasoning as DB-044.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_document_verifications', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('document_id')->constrained('documents')->cascadeOnDelete();

            $table->unsignedTinyInteger('completeness_score'); // 0-100, app-validated
            $table->json('issues')->nullable();
            $table->json('risk_flags')->nullable();

            $table->text('recommendation')->nullable(); // free-text advisory guidance, NOT AiRecommendation's enum
            $table->decimal('confidence', 3, 2)->nullable(); // 0.00-1.00, app-validated

            $table->json('citations')->nullable();

            $table->string('status')->default('ACTIVE'); // ACTIVE | SUPERSEDED — never deleted

            $table->boolean('ai_used')->default(false);
            $table->string('prompt_code')->nullable(); // PROMPT-004
            $table->string('prompt_version')->nullable();
            $table->string('model_code')->nullable();

            $table->timestamps();

            $table->index('document_id');
            $table->index('status');
            // Deliberately NO softDeletes() — permanent audit trail, see class doc comment above.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_document_verifications');
    }
};
