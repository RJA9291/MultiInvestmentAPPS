<?php

namespace App\Modules\Document\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** EVT-017 (07_EVENT_CATALOG.md) — Integration Event, consumed by Knowledge/Administration. */
class DocumentUploaded
{
    use Dispatchable, SerializesModels;

    /** @param  array<string, mixed>  $fileMetadata */
    public function __construct(
        public readonly string $documentId,
        public readonly string $projectId,
        public readonly string $documentType,
        public readonly array $fileMetadata,
    ) {
    }
}
