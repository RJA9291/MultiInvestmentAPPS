<?php

namespace App\Modules\Document\Application\Services;

use App\Modules\Document\Domain\Entities\DocumentVersion;
use App\Modules\Document\Domain\Events\DocumentVersionCreated;
use App\Modules\Document\Domain\Policies\FileTypeAllowlistPolicy;
use App\Modules\Document\Domain\Repositories\DocumentRepositoryInterface;
use App\Modules\Document\Domain\Repositories\DocumentVersionRepositoryInterface;
use App\Modules\Document\Infrastructure\Storage\FileStorageGatewayInterface;
use DomainException;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * DocumentVersioningService — proposed API-018 (POST /v1/documents/{id}/versions).
 *
 * Requires the EXISTING document_id explicitly, rather than the Project
 * Owner's sketch of matching "same name + project_id" to detect a new
 * version: `documents` (DB-006) has no such Business Key
 * (08_DATABASE_DESIGN.md §6 says so explicitly), and matching on a
 * user-supplied display name is fragile — two unrelated documents can
 * legitimately share a file name. The client already knows which Document
 * it is versioning (it fetched/listed it first), so asking for the id
 * directly is both simpler and correct.
 */
class DocumentVersioningService
{
    public function __construct(
        private readonly DocumentRepositoryInterface $documents,
        private readonly DocumentVersionRepositoryInterface $versions,
        private readonly FileStorageGatewayInterface $storage,
        private readonly FileTypeAllowlistPolicy $fileTypeAllowlistPolicy,
    ) {
    }

    public function uploadNewVersion(
        string $documentId,
        string $originalFileName,
        string $mimeType,
        string $contents,
        string $createdBy,
    ): DocumentVersion {
        if (! $this->fileTypeAllowlistPolicy->isAllowed($mimeType)) {
            throw new DomainException("File type '{$mimeType}' is not on the allow-list (BR-035/BR-130).");
        }

        $document = $this->documents->find($documentId);

        if (! $document) {
            throw new RuntimeException("Document {$documentId} not found.");
        }

        $nextVersionNumber = $this->versions->latestVersionNumber($documentId) + 1;

        $attachment = $this->storage->store($document->projectId(), $documentId, $nextVersionNumber, $originalFileName, $mimeType, $contents);

        $version = DocumentVersion::create((string) Str::uuid(), $documentId, $nextVersionNumber, $attachment, $createdBy);
        $this->versions->save($version);

        $document->attachNewVersion($attachment);
        $this->documents->save($document);

        DocumentVersionCreated::dispatch($documentId, $nextVersionNumber);

        return $version;
    }
}
