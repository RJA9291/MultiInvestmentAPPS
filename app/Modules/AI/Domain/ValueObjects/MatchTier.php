<?php

namespace App\Modules\AI\Domain\ValueObjects;

/** Matches the Project Owner's own §2 tier bands exactly — a display grouping only, never a decision. */
enum MatchTier: string
{
    case Low = 'LOW';
    case Medium = 'MEDIUM';
    case High = 'HIGH';

    public static function fromScore(int $score): self
    {
        return match (true) {
            $score >= 76 => self::High,
            $score >= 51 => self::Medium,
            default => self::Low,
        };
    }
}
