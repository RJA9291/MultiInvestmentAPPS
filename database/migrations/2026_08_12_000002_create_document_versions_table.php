<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DB-007: document_versions (Investment Context / Document Aggregate,
 * 08_DATABASE_DESIGN.md §6) — "version history for a Document, mirroring
 * `project_versions`' append-only design."
 *
 * This IS the locked versioning mechanism — a real Entity/table per version,
 * NOT the Project Owner's sketch (a `version` integer column on `documents`
 * that gets incremented in place while a whole new `documents` row is
 * inserted per upload). `documents` (DB-006) represents ONE logical
 * document's current state; this table holds its immutable history.
 *
 * Business Key is (document_id, version_number) — NOT (project_id, name) as
 * the Project Owner's sketch used to detect "same document, new version."
 * `document_type` there isn't a stored business key at all (§6's own
 * "Business Keys: none beyond id" note for DB-006) — so uploading a new
 * version requires the caller to supply the existing `document_id`
 * explicitly (see DocumentVersioningService), not a fragile name-string match.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('document_id')->constrained('documents')->cascadeOnDelete();
            $table->unsignedInteger('version_number');

            // This version's own Attachment column group — immutable once created.
            $table->uuid('file_id')->nullable();
            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('storage_path')->nullable(); // never exposed in any API response

            $table->uuid('created_by');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['document_id', 'version_number']);
            $table->index('document_id');
            // Append-only: no updated_at, no softDeletes().
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};
