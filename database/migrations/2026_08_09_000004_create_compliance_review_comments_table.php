<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DB-034: compliance_review_comments (08_DATABASE_DESIGN.md §14)
 * A rejection must have >= 1 comment (BR-141, RejectionRequiresReasonPolicy)
 * — enforced at the Application Service layer (ComplianceDecisionService),
 * not a DB CHECK constraint, since it depends on the sibling row(s) existing.
 * WAJIB: append-only, part of the permanent record (PDL-028) — no delete.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_review_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('compliance_review_id')->constrained('compliance_reviews')->cascadeOnDelete();
            $table->text('comment');
            $table->uuid('created_by')->nullable(); // resolves via user_identity_mappings, DB-036

            $table->timestamp('created_at')->useCurrent();

            $table->index('compliance_review_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_review_comments');
    }
};
