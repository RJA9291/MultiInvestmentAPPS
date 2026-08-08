<?php

namespace App\Modules\Document\Domain\Repositories;

use App\Modules\Document\Domain\Entities\Document;

interface DocumentRepositoryInterface
{
    public function find(string $id): ?Document;

    /** @return array<int, Document> */
    public function findByProjectId(string $projectId): array;

    public function save(Document $document): void;

    /** BR-032 — soft delete only, never a hard delete. */
    public function delete(string $id): void;
}
