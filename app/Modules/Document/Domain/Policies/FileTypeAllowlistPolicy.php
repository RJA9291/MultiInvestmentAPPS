<?php

namespace App\Modules\Document\Domain\Policies;

/**
 * FileTypeAllowlistPolicy (BR-035, BR-130)
 *
 * "A document's file type must be validated against an allow-list at
 * upload time, not a deny-list." The allow-list below is a reasonable
 * default for an investment data room (documents/spreadsheets/presentations/
 * images), FLAGGED as hardcoded rather than pulled from
 * `10_PLATFORM_GOVERNANCE.md`'s configuration mechanism — no such config
 * system exists in this codebase yet; making this list runtime-configurable
 * is a scoped future change, not invented here.
 */
class FileTypeAllowlistPolicy
{
    private const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
        'application/vnd.openxmlformats-officedocument.presentationml.presentation', // .pptx
        'image/png',
        'image/jpeg',
    ];

    public function isAllowed(string $mimeType): bool
    {
        return in_array($mimeType, self::ALLOWED_MIME_TYPES, true);
    }
}
