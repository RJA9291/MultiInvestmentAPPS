<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DB-010: nda_acknowledgments (Investment Context / DataRoomGrant Aggregate,
 * 08_DATABASE_DESIGN.md §7)
 *
 * Proof that a grantee accepted the NDA version gating their access
 * (BR-042, BR-133). Completely absent from the Project Owner's Data-Room
 * brief sketch — the sketch has no NDA concept at all — but this is already
 * a locked, mandatory part of the approved DataRoomGrant Aggregate and is
 * built here as originally designed, not skipped.
 *
 * `nda_version_hash` (BR-133): must capture the exact acknowledged NDA
 * version as a content hash, never just a boolean "accepted" flag — a
 * future NDA text change produces a new hash, and an old acknowledgment
 * never silently covers it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nda_acknowledgments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('data_room_grant_id')->constrained('data_room_grants')->cascadeOnDelete();
            $table->string('nda_version_hash');

            $table->timestamp('acknowledged_at')->useCurrent();
            $table->ipAddress('acknowledged_ip')->nullable();

            $table->unique(['data_room_grant_id', 'nda_version_hash']);
            $table->index('data_room_grant_id');
            // No soft delete — a legal proof-of-acceptance record, never mutated or removed.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nda_acknowledgments');
    }
};
