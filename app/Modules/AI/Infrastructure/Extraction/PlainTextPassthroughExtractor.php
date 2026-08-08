<?php

namespace App\Modules\AI\Infrastructure\Extraction;

use App\Modules\AI\Application\Contracts\DocumentContentExtractorInterface;
use App\Modules\Document\Domain\ValueObjects\Attachment;

/**
 * PlainTextPassthroughExtractor — an HONEST, scoped implementation, not a
 * fabricated one. It correctly extracts `text/plain` content by simply
 * returning the raw bytes; for every other mime type on the
 * FileTypeAllowlistPolicy (PDF, DOCX, XLSX, PPTX, PNG, JPEG), it returns an
 * empty string.
 *
 * WHY NOT MORE: no PDF-parsing, OCR, or Office-document library has been
 * verified as installed/available in this sandbox (no Composer/vendor —
 * see 00_MASTER_PROMPT.md's acknowledged sandbox limitation). Silently
 * pretending to extract text from a PDF via some invented parsing logic
 * would fabricate a capability this codebase does not actually have — the
 * same "never invent" principle already applied to NullAiProviderGateway
 * and WatermarkService. AiDocumentVerificationService compensates by
 * appending a flagged issue ("content extraction not supported for this
 * file type") to the result whenever this returns an empty string for a
 * non-empty file, so the gap is visible to the Compliance Officer rather
 * than silently degrading the AI's analysis.
 *
 * Promoting this to real PDF/OCR extraction is a scoped future change to
 * ONLY this class — nothing else in the pipeline needs to change.
 */
class PlainTextPassthroughExtractor implements DocumentContentExtractorInterface
{
    private const PLAIN_TEXT_MIME_TYPES = ['text/plain'];

    public function extract(Attachment $attachment, string $rawBytes): string
    {
        if (! in_array($attachment->mimeType, self::PLAIN_TEXT_MIME_TYPES, true)) {
            return '';
        }

        return $rawBytes;
    }
}
