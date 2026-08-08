<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DB-049: ai_investor_project_matches (AI Module, "AI Investor-Project
 * Matching Engine" brief, Sprint 12).
 *
 * RECONCILIATION vs. the Project Owner's proposed `ai_matches` schema:
 * (1) `$table->id()` (auto-increment) corrected to a UUID primary key
 * (DBR-001), applied directly per the standing precedent (confirmed
 * without exception across 48 prior tables); (2) `investor_id`/`project_id`
 * typed as `string` in the sketch are corrected to real, typed
 * `foreignUuid` FKs — `investor_profile_id` -> `investor_profiles` and
 * `project_id` -> `projects`. A REAL database FK is used here (not the
 * `UserReference`-style no-FK pattern) because both referenced tables
 * already live in the same Investment Context this AI-owned table reports
 * on, mirroring the exact precedent set by `ai_compliance_results.project_id`
 * (DB-044) and `ai_document_verifications.document_id` (DB-045) — both
 * real FKs from an AI-owned table into Investment Context; (3) no
 * `reason` TEXT column — replaced with `reasons` JSON (an array of
 * templated strings, one per scoring criterion, per `MatchScoringCalculator`'s
 * output), since a single free-text paragraph cannot be produced without
 * either concatenation gymnastics or a generative-AI call this pass
 * deliberately does not make (see `MatchScoringCalculator`'s own doc
 * comment); (4) `confidence` here means "how much real profile data backed
 * this score" (0.00-1.00), NOT "AI's confidence this is a good deal" —
 * a deliberately different meaning from `ai_compliance_results.confidence`,
 * consistent with this schema's established precedent of keeping
 * differently-scoped "confidence" concepts structurally separate rather
 * than conflating them.
 *
 * WAJIB: no soft/hard delete — `save()` (`updateOrCreate`) always upserts
 * the same (investor_profile_id, project_id) row rather than inserting a
 * new history row per run, since a match score is a current-state fact
 * ("how well does this pairing fit right now"), not an audited decision
 * requiring permanent history the way `ai_compliance_results` is.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_investor_project_matches', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('investor_profile_id')->constrained('investor_profiles')->cascadeOnDelete();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();

            $table->unsignedTinyInteger('score'); // 0-100, app-computed (MatchScoringCalculator)
            $table->json('breakdown')->nullable(); // per-criterion 0-100 scores
            $table->json('reasons')->nullable(); // templated, rule-based explanation strings — never free-form AI text
            $table->decimal('confidence', 3, 2)->nullable(); // 0.00-1.00, data-completeness confidence — NOT deal-quality confidence

            $table->timestamps();

            $table->unique(['investor_profile_id', 'project_id']);
            $table->index('project_id');
            $table->index('score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_investor_project_matches');
    }
};
