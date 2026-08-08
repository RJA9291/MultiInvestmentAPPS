<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DB-048: content_view_counts (Analytics/Reporting layer — 06_DOMAIN_MODEL.md
 * §12.x, added Sprint 12 for the Project Owner's "Dashboard & Analytics
 * Module" brief).
 *
 * RECONCILIATION vs. the Project Owner's sketch: the sketch proposed a
 * single generic `analytics_events` table (auto-increment id, free-string
 * `event_type`, nullable `user_id`/`project_id`, loose `metadata` json) as
 * a catch-all log for "DocumentViewed / ProjectViewed / AccessRequested /
 * AITriggered". This would duplicate the already-locked `audit_log`
 * (DB-032, ADR-004) almost exactly — DB-032 is already documented as "the
 * materialized, queryable projection of every auditable Domain Event
 * across the platform", explicitly the single choke point for this kind
 * of cross-context event logging. `AccessRequested` and the AI-triggered
 * events are ALREADY real, individually-catalogued Domain Events
 * (EVT-070, `AiCompliancePrecheckRequested`/`AiDocumentVerificationRequested`)
 * with their own typed payloads — collapsing them into a second, untyped
 * log would violate PDL-023's mandatory per-event metadata discipline.
 *
 * What genuinely did NOT exist yet: a plain "this Project/Document record
 * was looked at" counter (distinct from the already-locked, DataRoom-gated
 * EVT-024 `DataRoomDocumentViewed`). That gap is closed here, narrowly,
 * with two new typed events (EVT-073 `ProjectViewed`, EVT-074
 * `DocumentViewed`) feeding this single small counter table — not a
 * general-purpose event dump.
 *
 * UUID PK per DBR-001. `viewable_type`/`viewable_id` is a deliberately
 * generic polymorphic pair (values: 'project' | 'document') rather than
 * two nullable FK-shaped columns, since exactly one of the two kinds is
 * ever counted per row and a generic pair avoids a table shape that grows
 * a new nullable column per future viewable kind.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_view_counts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('viewable_type'); // 'project' | 'document'
            $table->uuid('viewable_id'); // ProjectReference or DocumentReference, no cross-context FK (PDL-020)

            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestamp('last_viewed_at')->nullable();

            $table->timestamps();

            $table->unique(['viewable_type', 'viewable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_view_counts');
    }
};
