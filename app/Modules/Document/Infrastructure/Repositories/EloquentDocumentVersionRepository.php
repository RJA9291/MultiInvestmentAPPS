<?php

namespace App\Modules\Document\Infrastructure\Repositories;

use App\Modules\Document\Domain\Repositories\DocumentVersionRepositoryInterface;
use App\Modules\Document\Domain\Entities\DocumentVersion;
use App\Modules\Document\Infrastructure\Eloquent\DocumentVersionModel;
use App\Modules\Document\Infrastructure\Mappers\DocumentVersionMapper;

class EloquentDocumentVersionRepository implements DocumentVersionRepositoryInterface
{
    public function __construct(private readonly DocumentVersionMapper $mapper)
    {
    }

    public function latestVersionNumber(string $documentId): int
    {
        return (int) (DocumentVersionModel::where('document_id', $documentId)->max('version_number') ?? 0);
    }

    public function listForDocument(string $documentId): array
    {
        return DocumentVersionModel::where('document_id', $documentId)
            ->orderBy('version_number')
            ->get()
            ->map(fn (DocumentVersionModel $model) => $this->mapper->toDomain($model))
            ->all();
    }

    public function save(DocumentVersion $version): void
    {
        $this->mapper->toModel($version)->save();
    }
}
