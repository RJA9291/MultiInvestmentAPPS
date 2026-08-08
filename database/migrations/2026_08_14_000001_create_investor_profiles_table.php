<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DB-046: investor_profiles (Investment Context, new InvestorProfile
 * Aggregate — see 06_DOMAIN_MODEL.md §5.x, added this Sprint)
 *
 * CRITICAL RECONCILIATION vs. the Project Owner's sketch: this is NOT a
 * parallel identity table. `users` (DB-001) is already "the system of
 * record for every human account (Business Owner, Investor, Admin)"
 * (06_DOMAIN_MODEL.md §2), with `investor` already a value of
 * `user_roles.role` (DB-002). The Project Owner's sketch's own `investors`
 * table — its own auto-increment `id`, its own unique `email`, its own
 * `name` — would fork identity into two independent systems the moment a
 * real Identity Module is built (not yet, this Sprint, same gap already
 * flagged throughout every other Module's `*_user_id` fields). Building it
 * anyway now would create a migration that has to be UNDONE later rather
 * than one that composes with the eventual Identity Module.
 *
 * Instead: `investor_user_id` is a plain `UserReference`-shaped UUID column
 * (08_DATABASE_DESIGN.md §3), no DB-level FK across contexts (PDL-020) —
 * the exact same pattern already used for `projects.owner_user_id`,
 * `data_room_grants.grantee_user_id`, etc. This table holds ONLY the
 * investment-specific extension data a User account does not have: KYC-style
 * verification status, investor type, and the optional profile fields the
 * Project Owner flagged for future AI matching.
 *
 * UUID PK per DBR-001 — the Project Owner's sketch used `$table->id()`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investor_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('investor_user_id')->unique(); // UserReference — one profile per user, no DB FK (PDL-020)

            $table->string('investor_type'); // INDIVIDUAL | COMPANY
            $table->string('verification_status')->default('PENDING'); // PENDING | VERIFIED | REJECTED

            // Optional extension fields (Project Owner's brief §7) — nullable,
            // populated progressively, feeding a future AI matching capability
            // (§8 of the brief) that is NOT built in this pass (no AGENT-XXX/
            // PROMPT-XXX registered for investor-project matching yet — not
            // fabricated here, same "never invent a capability" discipline).
            $table->string('company_name')->nullable();
            $table->string('investment_range')->nullable();
            $table->string('preferred_industry')->nullable();
            $table->string('risk_appetite')->nullable();

            $table->uuid('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            $table->index('verification_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investor_profiles');
    }
};
