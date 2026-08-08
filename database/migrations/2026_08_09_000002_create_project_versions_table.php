<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DB-005: project_versions (08_DATABASE_DESIGN.md §5)
 * Immutable snapshots of a Project's content at each version (BR-025).
 * WAJIB: append-only — no UPDATE at the application layer once inserted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->unsignedInteger('version_number');

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->nullable();

            $table->uuid('created_by')->nullable(); // UserReference

            $table->timestamp('created_at')->useCurrent(); // append-only: no updated_at

            $table->unique(['project_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_versions');
    }
};
