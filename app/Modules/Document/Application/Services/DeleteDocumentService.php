<?php

namespace App\Modules\Document\Application\Services;

use App\Modules\Document\Domain\Events\DocumentDeleted;
use App\Modules\Document\Domain\Repositories\DocumentRepositoryInterface;

/** DeleteDocumentService — proposed API-021. BR-032: soft delete only, never a hard delete. */
class DeleteDocumentService
{
    public function __construct(private readonly DocumentRepositoryInterface $documents)
    {
    }

    public function execute(string $documentId, string $deletedBy): void
    {
        $this->documents->delete($documentId);

        DocumentDeleted::dispatch($documentId, $deletedBy);
    }
}
