<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DB-035: compliance_review_attachments (08_DATABASE_DESIGN.md §14)
 * Supporting evidence a reviewer attaches. Uses the Attachment Value Object
 * column group (§3 of 08_DATABASE_DESIGN.md) for the file pointer itself.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_review_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('compliance_review_id')->constrained('compliance_reviews')->cascadeOnDelete();

            // Attachment Value Object column group (08_DATABASE_DESIGN.md §3):
            $table->uuid('file_id')->nullable();
            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();

            $table->uuid('created_by')->nullable(); // resolves via user_identity_mappings, DB-036

            $table->timestamp('created_at')->useCurrent();

            $table->index('compliance_review_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_review_attachments');
    }
};
