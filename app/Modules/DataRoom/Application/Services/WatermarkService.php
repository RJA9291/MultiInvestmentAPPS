<?php

namespace App\Modules\DataRoom\Application\Services;

/**
 * WatermarkService (06_DOMAIN_MODEL.md §3.3) — computes the dynamic,
 * per-user watermark TEXT ("User: investor@abc.com / Access Time:
 * 2026-08-08 14:32", per the Project Owner's brief §8).
 *
 * FLAGGED, not invented: this class produces the watermark's content only.
 * Actually burning that text into a served PDF/image (rather than just
 * returning a string) requires a real PDF/image-manipulation library
 * (e.g. setasign/fpdi, spatie/pdf-to-image) — this sandbox has no
 * vendor/Composer to install, evaluate, or verify one, so no such
 * integration is claimed here. AccessDocumentController currently returns
 * this text as response metadata alongside the access decision, not a
 * watermarked file stream. Wiring an actual library call is a concrete,
 * scoped follow-up once `composer install` is possible, not a stub pretending
 * to already work.
 *
 * Applied only to ViewOnly content, per PermissionTier's own doc comment —
 * watermarking is this Sprint's realization of "protect content that isn't
 * downloadable," not a 4th access tier.
 */
class WatermarkService
{
    public function computeWatermarkText(string $granteeIdentifier, \DateTimeInterface $accessTime): string
    {
        return sprintf(
            'User: %s | Access Time: %s',
            $granteeIdentifier,
            $accessTime->format('Y-m-d H:i')
        );
    }
}
