<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DB-028: notifications (Notification Context, 06_DOMAIN_MODEL.md §8 —
 * already locked BEFORE this Sprint's Notification Module brief arrived).
 *
 * RECONCILIATION vs. the Project Owner's sketch: the sketch's own
 * `notifications` table used `$table->id()` (auto-increment, corrected to
 * UUID per DBR-001), a `status` string column with `UNREAD`/`READ` values
 * (corrected to a boolean `is_read`, per DB-028's own already-locked shape
 * — a two-value lifecycle doesn't need a string enum), and only
 * `created_at`/`updated_at` (corrected to the locked `queued_at`/
 * `delivered_at`/`read_at` triple, since `06_DOMAIN_MODEL.md` §8 models
 * queuing, delivery, and reading as three distinct Domain Events —
 * EVT-052/053/054 — not one undifferentiated timestamp). `user_id` renamed
 * to `recipient_user_id` to match DB-028's own already-locked column name.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('recipient_user_id'); // UserReference, no DB FK (PDL-020)

            $table->string('notification_type');
            $table->string('title');
            $table->text('message');
            $table->json('metadata')->nullable();

            $table->boolean('is_read')->default(false);

            $table->timestamp('queued_at')->useCurrent();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index('recipient_user_id');
            $table->index('is_read');
            // Soft Delete: No (DB-028's own spec) — a read notification is
            // flagged via is_read/read_at, never deleted, so NotificationRead
            // history stays queryable.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
