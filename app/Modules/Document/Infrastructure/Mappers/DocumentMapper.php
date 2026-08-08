<?php

namespace App\Modules\Document\Infrastructure\Mappers;

use App\Modules\Document\Domain\Entities\Document;
use App\Modules\Document\Domain\ValueObjects\Attachment;
use App\Modules\Document\Infrastructure\Eloquent\DocumentModel;

class DocumentMapper
{
    public function toDomain(DocumentModel $model): Document
    {
        return Document::reconstitute(
            id: $model->id,
            projectId: $model->project_id,
            documentType: $model->document_type,
            currentAttachment: new Attachment($model->file_id, $model->file_name, $model->mime_type, $model->storage_path),
            isApproved: $model->is_approved,
            uploadedBy: $model->uploaded_by,
            uploadedAt: $model->uploaded_at,
            approvedBy: $model->approved_by,
            approvedAt: $model->approved_at,
        );
    }

    public function toModel(Document $document, ?DocumentModel $existing = null): DocumentModel
    {
        $model = $existing ?? new DocumentModel();

        if (! $existing) {
            $model->id = $document->id(); // deliberately not fillable — see ProjectMapper's precedent
            $model->project_id = $document->projectId();
            $model->document_type = $document->documentType();
            $model->uploaded_by = $document->uploadedBy();
            $model->uploaded_at = $document->uploadedAt();
        }

        $attachment = $document->currentAttachment();
        $model->file_id = $attachment->fileId;
        $model->file_name = $attachment->fileName;
        $model->mime_type = $attachment->mimeType;
        $model->storage_path = $attachment->storagePath;

        $model->is_approved = $document->isApproved();
        $model->approved_by = $document->approvedBy();
        $model->approved_at = $document->approvedAt();

        return $model;
    }
}
