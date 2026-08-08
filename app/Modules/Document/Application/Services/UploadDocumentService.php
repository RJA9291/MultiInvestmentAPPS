<?php

namespace App\Modules\Document\Application\Services;

use App\Modules\Document\Domain\Entities\Document;
use App\Modules\Document\Domain\Entities\DocumentVersion;
use App\Modules\Document\Domain\Events\DocumentUploaded;
use App\Modules\Document\Domain\Events\DocumentVersionCreated;
use App\Modules\Document\Domain\Policies\FileTypeAllowlistPolicy;
use App\Modules\Document\Domain\Repositories\DocumentRepositoryInterface;
use App\Modules\Document\Domain\Repositories\DocumentVersionRepositoryInterface;
use App\Modules\Document\Infrastructure\Storage\FileStorageGatewayInterface;
use DomainException;
use Illuminate\Support\Str;

/**
 * UploadDocumentService — API-010 (POST /v1/documents), the FIRST upload of
 * a new logical document. Uploading a subsequent version of an EXISTING
 * document is DocumentVersioningService::uploadNewVersion() instead — see
 * that class's doc comment for why "same name" matching (the Project
 * Owner's sketch) was not used to distinguish the two cases.
 */
class UploadDocumentService
{
    public function __construct(
        private readonly DocumentRepositoryInterface $documents,
        private readonly DocumentVersionRepositoryInterface $versions,
        private readonly FileStorageGatewayInterface $storage,
        private readonly FileTypeAllowlistPolicy $fileTypeAllowlistPolicy,
    ) {
    }

    public function execute(
        string $projectId,
        string $documentType,
        string $originalFileName,
        string $mimeType,
        string $contents,
        string $uploadedBy,
    ): Document {
        if (! $this->fileTypeAllowlistPolicy->isAllowed($mimeType)) {
            throw new DomainException("File type '{$mimeType}' is not on the allow-list (BR-035/BR-130).");
        }

        $documentId = (string) Str::uuid();

        $attachment = $this->storage->store($projectId, $documentId, 1, $originalFileName, $mimeType, $contents);

        $document = Document::upload($documentId, $projectId, $documentType, $attachment, $uploadedBy);
        $this->documents->save($document);

        $version = DocumentVersion::create((string) Str::uuid(), $documentId, 1, $attachment, $uploadedBy);
        $this->versions->save($version);

        DocumentUploaded::dispatch($documentId, $projectId, $documentType, [
            'file_name' => $attachment->fileName,
            'mime_type' => $attachment->mimeType,
        ]);
        DocumentVersionCreated::dispatch($documentId, 1);

        return $document;
    }
}
