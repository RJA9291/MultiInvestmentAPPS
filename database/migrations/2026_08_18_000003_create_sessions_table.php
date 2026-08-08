<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DB-003: sessions (Identity Context, User Aggregate) — backs
 * SessionExpiryPolicy (BR-013) and SEC-010's session control. `token_hash`
 * is `sha256(jwt)`, never the raw JWT (locked "Encryption Required: Yes —
 * token_hash only, never the raw token"). No Soft Delete — an
 * expired/logged-out session is left with `ended_at` populated.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            // Nullable + unique: briefly NULL between start() and finalize()
            // within the same request (see SessionStore) — a nullable unique
            // column permits multiple NULLs on every mainstream DB engine
            // (Postgres, MySQL, SQLite), so this never collides.
            $table->string('token_hash')->nullable()->unique();
            $table->timestamp('expires_at');
            $table->string('ip_address')->nullable();
            $table->timestamp('ended_at')->nullable();

            $table->timestamp('created_at')->useCurrent(); // login time — no updated_at (rows are append/end-only)

            $table->index('user_id');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
