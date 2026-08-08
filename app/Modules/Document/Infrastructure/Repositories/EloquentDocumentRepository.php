<?php

namespace App\Modules\Document\Infrastructure\Repositories;

use App\Modules\Document\Domain\Entities\Document;
use App\Modules\Document\Domain\Repositories\DocumentRepositoryInterface;
use App\Modules\Document\Infrastructure\Eloquent\DocumentModel;
use App\Modules\Document\Infrastructure\Mappers\DocumentMapper;

class EloquentDocumentRepository implements DocumentRepositoryInterface
{
    public function __construct(private readonly DocumentMapper $mapper)
    {
    }

    public function find(string $id): ?Document
    {
        $model = DocumentModel::find($id);

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findByProjectId(string $projectId): array
    {
        return DocumentModel::where('project_id', $projectId)
            ->get()
            ->map(fn (DocumentModel $model) => $this->mapper->toDomain($model))
            ->all();
    }

    public function save(Document $document): void
    {
        $existing = DocumentModel::find($document->id());
        $this->mapper->toModel($document, $existing)->save();
    }

    public function delete(string $id): void
    {
        DocumentModel::find($id)?->delete(); // soft delete, BR-032 — SoftDeletes trait makes this non-destructive
    }
}
