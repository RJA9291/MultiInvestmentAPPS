<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DB-006: documents (Investment Context / Document Aggregate,
 * 06_DOMAIN_MODEL.md §3.2, 08_DATABASE_DESIGN.md §6)
 *
 * "The actual file bytes live in Cross-Cutting File Storage — this table
 * never stores file content." `file_id`/`file_name`/`mime_type` is the
 * Attachment column group (08_DATABASE_DESIGN.md §3), NOT a raw `file_path`
 * string column — the Project Owner's own Document-brief Security Rules
 * ("JANGAN expose file_path direct") are exactly why the locked design
 * already avoids a raw path column here.
 *
 * `is_approved` (boolean) + `deleted_at` (soft delete, BR-032) is the full
 * locked status model — NOT the Project Owner's proposed 6-value
 * DRAFT/UPLOADED/UNDER_REVIEW/APPROVED/REJECTED/ARCHIVED enum. "Approved"
 * per the locked Domain Model means the document "passes malware/type/
 * completeness checks and is eligible to count toward PublishEligibilityPolicy
 * (BR-017)" — an automated ingest check, not a human review decision (human
 * review of a Project as a whole is already the separately-built
 * ComplianceReview Aggregate). "Archived" maps to `deleted_at` being set
 * (BR-032: soft-deleted, no longer visible to Investors, still available
 * for audit/AI-citation reference).
 *
 * `document_type` (not `type`) is restricted to an allow-list at the
 * application layer (BR-035/BR-130 FileTypeAllowlistPolicy).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();

            $table->string('document_type'); // allow-list validated at app layer, BR-035/BR-130

            // Attachment column group (08_DATABASE_DESIGN.md §3) — current/latest
            // version's file, kept in sync on each new DocumentVersion.
            // FLAGGED: file_id is a locally-generated UUID identifier, not a
            // reference into a real Cross-Cutting File Storage registry table
            // (06_DOMAIN_MODEL.md §11) — that service does not exist as a
            // built table in this codebase yet.
            $table->uuid('file_id')->nullable();
            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();
            // Private-disk-relative path — deliberately never exposed in any
            // API response (see DocumentResource); meaningful only to
            // FileStorageGatewayInterface's own implementation.
            $table->string('storage_path')->nullable();

            $table->boolean('is_approved')->default(false);

            $table->uuid('uploaded_by');
            $table->timestamp('uploaded_at')->useCurrent();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes(); // BR-032 SoftDeletePolicy — WAJIB, unlike the registry tables' no-soft-delete rule

            $table->index('project_id');
            $table->index('document_type');
            $table->index('is_approved');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
