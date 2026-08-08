<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DB-001: users (Identity Context, User Aggregate — 06_DOMAIN_MODEL.md §2).
 *
 * This table has been referenced by every other Module built this Sprint
 * (`projects.owner_user_id`, `investor_profiles.investor_user_id`,
 * `data_room_grants.grantee_user_id`, etc.) as a `UserReference`-shaped
 * UUID with no cross-context DB FK (PDL-020) — this migration is what
 * finally makes those references resolvable to a real row.
 *
 * `name` (mapped to `display_name` at the Eloquent/Domain layer as
 * `displayName`) is not called out as its own bullet in
 * `08_DATABASE_DESIGN.md`'s DB-001 write-up, but that write-up's own PII
 * Classification line ("email, name, authentication credentials") and the
 * Shared Kernel `UserReference` VO ("ID + display name", §13) both require
 * it — added to close that dependency, not invented independently.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('display_name');
            $table->string('email')->unique();
            $table->string('password_hash');

            $table->boolean('mfa_enabled')->default(false);
            $table->boolean('is_suspended')->default(false);

            $table->timestamps();
            $table->softDeletes(); // BR-008: a deactivated user is soft-deleted, never hard-deleted

            $table->index('is_suspended');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
