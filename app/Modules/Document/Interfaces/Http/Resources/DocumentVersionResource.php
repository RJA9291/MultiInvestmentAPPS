<?php

namespace App\Modules\Document\Interfaces\Http\Resources;

use App\Modules\Document\Domain\Entities\DocumentVersion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin DocumentVersion */
class DocumentVersionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var DocumentVersion $version */
        $version = $this->resource;

        return [
            'id' => $version->id(),
            'document_id' => $version->documentId(),
            'version_number' => $version->versionNumber(),
            'file_name' => $version->attachment()->fileName,
            'mime_type' => $version->attachment()->mimeType,
            'created_by' => $version->createdBy(),
            'created_at' => $version->createdAt()->toIso8601String(),
        ];
    }
}
