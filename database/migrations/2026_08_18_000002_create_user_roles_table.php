<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DB-002: user_roles (Identity Context, User Aggregate) — the combinable
 * RoleSet Value Object (BR-002) implemented as a queryable pivot so role
 * history is preserved for UserRoleAssigned/EVT-002 auditing. Revocation
 * is `revoked_at` being set, never a row delete (locked "Soft Delete: No"
 * note — the grant itself must remain queryable).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role'); // business_owner | investor | admin | compliance_officer (BR-139)

            $table->timestamp('granted_at');
            $table->uuid('granted_by')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->uuid('revoked_by')->nullable();

            $table->index('user_id');
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
