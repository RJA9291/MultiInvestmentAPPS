<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DB-047: access_requests (Investment Context, new AccessRequest Aggregate)
 *
 * An investor's request for Data Room access to a specific Project. UUID PK
 * per DBR-001 (sketch used `$table->id()`); `project_id` is a real
 * same-context FK to `projects` (DB-004); `investor_user_id` is a
 * `UserReference`-shaped UUID (no FK, PDL-020), matching DB-046 and every
 * other cross-context user reference in this schema.
 *
 * `status` mirrors `compliance_reviews`' immutable-once-decided discipline
 * (BR-140/PDL-027 pattern, applied here as a new rule — see
 * `04_BUSINESS_RULES.md`): once `APPROVED` or `REJECTED`, a row is never
 * mutated again at the application layer; a changed mind requires a new
 * request, not reopening this one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->uuid('investor_user_id'); // UserReference, no DB FK (PDL-020)

            $table->string('status')->default('PENDING'); // PENDING | APPROVED | REJECTED

            $table->timestamp('requested_at')->useCurrent();
            $table->uuid('decided_by')->nullable();
            $table->timestamp('decided_at')->nullable();

            $table->timestamps();

            $table->index('project_id');
            $table->index('investor_user_id');
            $table->index('status');
            // One PENDING request per (project_id, investor_user_id) at a time,
            // enforced at the application layer (RequestProjectAccessService),
            // not a DB unique constraint — a prior REJECTED/APPROVED row must
            // remain queryable, so a partial-unique-index approach was not used.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_requests');
    }
};
