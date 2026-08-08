<?php

namespace App\Modules\Document\Infrastructure\Storage;

use App\Modules\Document\Domain\ValueObjects\Attachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * LocalFileStorageGateway — stores documents on Laravel's `local` disk
 * (private by default, unlike `public`), under
 * `documents/{project_id}/{document_id}/{file_id}_{file_name}` — keyed by
 * `document_id`/`file_id`, not by document NAME as the Project Owner's
 * sketch suggested (`documents/{project_id}/{document_name}/v{version}.pdf`)
 * — a name-keyed path is fragile (unsafe characters, collisions between
 * unrelated documents that happen to share a display name) and doesn't
 * match DB-006's own "Business Keys: none beyond id" note.
 *
 * SECURITY RULE (WAJIB, Project Owner's brief §9): this disk is never
 * exposed via a public URL — `retrieve()` returns raw bytes for a
 * Controller to stream through the application, the only sanctioned path
 * (`Storage::disk('local')` has no public URL at all, unlike `public`).
 *
 * FLAGGED: this is a Sprint 12 scoped implementation, not the full
 * Cross-Cutting File Storage service `06_DOMAIN_MODEL.md` §11 describes
 * (which would likely centralize storage for every Module, not just
 * Document). Promoting this into that shared service is a future
 * consolidation, not invented as already done here.
 */
class LocalFileStorageGateway implements FileStorageGatewayInterface
{
    public function store(
        string $projectId,
        string $documentId,
        int $versionNumber,
        string $originalFileName,
        string $mimeType,
        string $contents,
    ): Attachment {
        $fileId = (string) Str::uuid();
        $path = "documents/{$projectId}/{$documentId}/{$fileId}_{$originalFileName}";

        Storage::disk('local')->put($path, $contents);

        return new Attachment($fileId, $originalFileName, $mimeType, $path);
    }

    public function retrieve(Attachment $attachment): string
    {
        return Storage::disk('local')->get($attachment->storagePath);
    }
}
