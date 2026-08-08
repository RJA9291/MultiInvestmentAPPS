<?php

namespace App\Modules\AI\Application\Contracts;

use App\Modules\Document\Domain\ValueObjects\Attachment;

/**
 * DocumentContentExtractorInterface — the port between a stored Document's
 * Attachment and text an AI provider can read. Only depends on Document
 * Module's public Attachment Value Object (interface-only cross-Module
 * dependency, PDL-020) — never touches Storage/`documents` directly.
 */
interface DocumentContentExtractorInterface
{
    public function extract(Attachment $attachment, string $rawBytes): string;
}
