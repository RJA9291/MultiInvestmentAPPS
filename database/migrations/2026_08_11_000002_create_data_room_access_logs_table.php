<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DB-009: data_room_access_logs (Investment Context / DataRoomGrant Aggregate,
 * 08_DATABASE_DESIGN.md §7)
 *
 * Every view/download event against a grant — "this table IS the audit
 * record" per the locked design. Append-only: application code must never
 * UPDATE or DELETE a row here (08_DATABASE_DESIGN.md's append-only-tables
 * Business Rule already names this table explicitly).
 *
 * `ip_address` is a FLAGGED promotion: the locked design's own Future
 * Expansion note said "IP/geolocation of access, if a future NFR requires
 * it" — not yet needed at the time DB-009 was approved. The Project Owner's
 * Data-Room brief explicitly marks IP logging WAJIB, so it is added now
 * rather than deferred further; tracked in `08_DATABASE_DESIGN.md`'s next
 * revision, not silently added.
 *
 * `occurred_at` (not `created_at`) is the locked column name for this
 * table's timestamp, matching 08_DATABASE_DESIGN.md's own Audit field name.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_room_access_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('data_room_grant_id')->constrained('data_room_grants')->cascadeOnDelete();

            $table->string('action'); // 'viewed' | 'downloaded' — EVT-024 / EVT-025
            $table->ipAddress('ip_address')->nullable(); // FLAGGED promotion — see class doc comment

            $table->timestamp('occurred_at')->useCurrent();

            $table->index('data_room_grant_id');
            $table->index('occurred_at');
            // Append-only: no updated_at, no softDeletes().
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_room_access_logs');
    }
};
