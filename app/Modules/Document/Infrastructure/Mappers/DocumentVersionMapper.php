<?php

namespace App\Modules\Document\Infrastructure\Mappers;

use App\Modules\Document\Domain\Entities\DocumentVersion;
use App\Modules\Document\Domain\ValueObjects\Attachment;
use App\Modules\Document\Infrastructure\Eloquent\DocumentVersionModel;

class DocumentVersionMapper
{
    public function toDomain(DocumentVersionModel $model): DocumentVersion
    {
        return DocumentVersion::reconstitute(
            id: $model->id,
            documentId: $model->document_id,
            versionNumber: $model->version_number,
            attachment: new Attachment($model->file_id, $model->file_name, $model->mime_type, $model->storage_path),
            createdBy: $model->created_by,
            createdAt: $model->created_at,
        );
    }

    public function toModel(DocumentVersion $version): DocumentVersionModel
    {
        $model = new DocumentVersionModel(); // never updated once created
        $model->id = $version->id();
        $model->document_id = $version->documentId();
        $model->version_number = $version->versionNumber();
        $model->file_id = $version->attachment()->fileId;
        $model->file_name = $version->attachment()->fileName;
        $model->mime_type = $version->attachment()->mimeType;
        $model->storage_path = $version->attachment()->storagePath;
        $model->created_by = $version->createdBy();
        $model->created_at = $version->createdAt();

        return $model;
    }
}
