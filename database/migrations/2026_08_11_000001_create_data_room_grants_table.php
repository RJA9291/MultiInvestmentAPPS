<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DB-008: data_room_grants (Investment Context / DataRoomGrant Aggregate,
 * 06_DOMAIN_MODEL.md §3.3, 08_DATABASE_DESIGN.md §7, ADR-002)
 *
 * One row per document-per-authorized-user grant — NOT per-project. This is
 * the locked, already-approved design and is intentionally more granular
 * than the Project Owner's own Data-Room-brief sketch (which grants at the
 * project level): an investor may be permitted some documents in a data
 * room but not others, which per-document grants support and per-project
 * grants cannot.
 *
 * `document_id` deliberately has NO database foreign key here: the Document
 * Module's `documents` table (DB-006) has not been built in this Sprint 12
 * pass. The locked design calls this a same-context FK once that table
 * exists; omitted here as a flagged forward reference, not invented.
 *
 * `permission_tier` is a 2-value enum (`view_only`, `downloadable`) per
 * BR-041 — NOT the Project Owner's proposed 4-tier scheme
 * (VIEW_ONLY/VIEW_WITH_WATERMARK/DOWNLOAD_ALLOWED/FULL_ACCESS). Watermarking
 * is treated as a rendering behavior always applied to view-only content
 * (WatermarkService), not a separate access tier — see that class's doc
 * comment.
 *
 * `expires_at` is a FLAGGED ADDITIVE column beyond the locked design: DB-008
 * as originally approved has no expiration concept, only revocation
 * (`revoked_at`). The Project Owner's Data-Room brief explicitly asked for
 * time-bound grants; adding it here is backward-compatible (nullable, no
 * existing behavior changes when null) and tracked in
 * `08_DATABASE_DESIGN.md`'s next revision rather than silently added.
 *
 * WAJIB: no soft delete — revocation uses `revoked_at`, never a delete, so
 * the historical grant remains auditable (BR-045 `ImmediateRevocationPolicy`
 * — "immediate" means the query-time check, not that the row disappears).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_room_grants', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('document_id'); // same-context FK to DB-006 once Document Module exists — flagged, not enforced yet
            $table->uuid('grantee_user_id'); // UserReference, §3 — no FK, cross-context style reference

            $table->string('permission_tier')->default('view_only'); // BR-041 DefaultViewOnlyPolicy

            $table->uuid('granted_by');
            $table->timestamp('granted_at')->useCurrent();
            $table->uuid('revoked_by')->nullable();
            $table->timestamp('revoked_at')->nullable();

            $table->timestamp('expires_at')->nullable(); // FLAGGED additive column — see class doc comment

            $table->timestamps();

            $table->index('document_id');
            $table->index('grantee_user_id');
            $table->index('revoked_at');
            // Deliberately NO softDeletes() — see class doc comment above.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_room_grants');
    }
};
