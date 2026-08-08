<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DB-033: compliance_reviews (Compliance Context, ComplianceReview Aggregate —
 * 08_DATABASE_DESIGN.md §14)
 *
 * One row per review CYCLE — a rejected-and-resubmitted Project produces a NEW
 * row here, never an edit to the old one (PDL-027). Once status leaves
 * 'pending', the row is immutable at the application layer (BR-140/PDL-027,
 * ImmutableDecisionPolicy) — enforced in code, not a DB trigger, so the rule
 * stays visible.
 *
 * WAJIB: no soft/hard delete — permanent history is the entire point (PDL-028).
 *
 * `reviewer_user_id` resolves identity via `user_identity_mappings` (DB-036,
 * ADR-012/PDL-043), never via a `users` table directly, per the pseudonymization
 * model — this table stores only the opaque user_id reference.
 *
 * `decision_made_by` / `decision_source` (PDL-059, 00_MASTER_PROMPT.md v3.18.0):
 * `decision_made_by` renamed from the original `decided_by` for exact PDL-059
 * wording. `decision_source` is a provenance/audit field only — it records
 * whether the deciding Compliance Officer consulted an AI pre-check
 * (`AI_ASSISTED`) or not (`HUMAN`, the default). It NEVER means AI made the
 * decision: `ComplianceDecisionService`'s `ComplianceOfficerOnlyPolicy` gate
 * (BR-139, PDL-053) applies identically regardless of this column's value.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->unsignedInteger('cycle_number')->default(1);

            $table->uuid('reviewer_user_id')->nullable(); // resolves via user_identity_mappings, DB-036
            $table->string('status')->default('pending'); // pending, approved, rejected

            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('decided_at')->nullable();
            $table->uuid('decision_made_by')->nullable(); // resolves via user_identity_mappings, DB-036; was `decided_by`
            $table->string('decision_source')->nullable(); // 'HUMAN' | 'AI_ASSISTED' — provenance only, PDL-059

            $table->timestamps();

            $table->index('project_id');
            $table->index('status');
            // Deliberately NO softDeletes() — PDL-028 permanent history.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_reviews');
    }
};
