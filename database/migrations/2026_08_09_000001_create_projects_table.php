<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DB-004: projects (Investment Context, Project Aggregate — 08_DATABASE_DESIGN.md §5)
 *
 * Lifecycle (06_DOMAIN_MODEL.md §18): Draft -> Submitted -> UnderComplianceReview
 * -> Approved -> Published -> Archived, with Unpublished as a manual reversible
 * side-transition from Published, and a rejection branch:
 * UnderComplianceReview -> ReturnedToBusinessOwner -> (edits) -> Submitted (PDL-027).
 *
 * WAJIB: no financial/monetary column exists on this table at all — structurally
 * enforced per BR-020/BR-028 (NoFinancialFieldPolicy), not a naming convention.
 * `owner_user_id` is a plain UUID (UserReference, Shared Kernel §3) — NO foreign
 * key across Bounded Contexts, per PDL-020.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('project_code')->unique();
            $table->uuid('owner_user_id'); // UserReference — no cross-context FK, PDL-020

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->nullable(); // ProjectCategory value object

            $table->string('status')->default('draft');
            // enum values: draft, submitted, under_compliance_review, approved,
            // published, unpublished, archived, returned_to_business_owner

            $table->uuid('current_compliance_review_id')->nullable(); // plain ref, no cross-context FK

            $table->softDeletes(); // Yes, per §5 — a Domain Context table, not a registry
            $table->timestamps();

            $table->index('status');
            $table->index('owner_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
