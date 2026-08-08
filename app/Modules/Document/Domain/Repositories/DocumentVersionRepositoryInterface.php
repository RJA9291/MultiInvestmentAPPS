<?php

namespace App\Modules\Document\Domain\Repositories;

use App\Modules\Document\Domain\Entities\DocumentVersion;

interface DocumentVersionRepositoryInterface
{
    public function latestVersionNumber(string $documentId): int;

    /** @return array<int, DocumentVersion> */
    public function listForDocument(string $documentId): array;

    public function save(DocumentVersion $version): void;
}
