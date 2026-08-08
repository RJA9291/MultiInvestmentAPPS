<?php

namespace App\Modules\Document\Infrastructure\Storage;

use App\Modules\Document\Domain\ValueObjects\Attachment;

/**
 * FileStorageGatewayInterface — the realization of "Cross-Cutting File
 * Storage" (06_DOMAIN_MODEL.md §11) that the Document Aggregate's Attachment
 * column group points to. No Domain or Application class in this Module
 * touches Laravel's Storage facade directly — only the concrete
 * implementation of this interface does.
 */
interface FileStorageGatewayInterface
{
    /**
     * Stores file contents privately and returns the resulting Attachment
     * pointer. Never returns a public URL — see LocalFileStorageGateway's
     * doc comment for the private-disk guarantee.
     */
    public function store(
        string $projectId,
        string $documentId,
        int $versionNumber,
        string $originalFileName,
        string $mimeType,
        string $contents,
    ): Attachment;

    /** Raw bytes for a stored Attachment — the ONLY path that may read file content, used by Controllers streaming a response. */
    public function retrieve(Attachment $attachment): string;
}
