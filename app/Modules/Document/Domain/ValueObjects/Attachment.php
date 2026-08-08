<?php

namespace App\Modules\Document\Domain\ValueObjects;

/**
 * Attachment — the Shared Kernel column group (08_DATABASE_DESIGN.md §3):
 * `file_id`, `file_name`, `mime_type`. Never carries a public URL — nothing
 * in this Value Object is servable directly by a browser, satisfying the
 * Project Owner's Security Rule ("JANGAN expose file_path direct, allow
 * public URL"). `storagePath` IS present, but it is a private-disk-relative
 * path meaningful only to FileStorageGatewayInterface's own implementation
 * — it is deliberately never serialized into any HTTP Resource/response
 * (see DocumentResource, which omits it entirely).
 */
final class Attachment
{
    public function __construct(
        public readonly string $fileId,
        public readonly string $fileName,
        public readonly string $mimeType,
        public readonly string $storagePath,
    ) {
    }
}
