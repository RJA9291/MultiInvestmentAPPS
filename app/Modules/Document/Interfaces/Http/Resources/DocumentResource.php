<?php

namespace App\Modules\Document\Interfaces\Http\Resources;

use App\Modules\Document\Domain\Entities\Document;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Document
 *
 * SECURITY RULE (WAJIB): never expose `file_id`'s underlying storage path
 * or any public URL — `storagePath` is deliberately omitted from this
 * array. `file_name`/`mime_type` are safe display metadata only.
 */
class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Document $document */
        $document = $this->resource;
        $attachment = $document->currentAttachment();

        return [
            'id' => $document->id(),
            'project_id' => $document->projectId(),
            'document_type' => $document->documentType(),
            'file_name' => $attachment->fileName,
            'mime_type' => $attachment->mimeType,
            'is_approved' => $document->isApproved(),
            'uploaded_by' => $document->uploadedBy(),
            'uploaded_at' => $document->uploadedAt()->toIso8601String(),
            'approved_by' => $document->approvedBy(),
            'approved_at' => $document->approvedAt()?->toIso8601String(),
        ];
    }
}
