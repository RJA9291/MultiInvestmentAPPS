<?php

namespace App\Modules\DataRoom\Domain\ValueObjects;

/**
 * PermissionTier (BR-041, 08_DATABASE_DESIGN.md DB-008)
 *
 * Deliberately 2 values, not the Project Owner's proposed 4-tier scheme.
 * Watermarking is NOT a tier here — it is a rendering behavior
 * (WatermarkService) always applied when serving ViewOnly content, and a
 * 4th "FULL_ACCESS" tier would be ambiguous with Downloadable. This is the
 * already-locked design (06_DOMAIN_MODEL.md §3.3), not a new decision.
 */
enum PermissionTier: string
{
    case ViewOnly = 'view_only';
    case Downloadable = 'downloadable';
}
