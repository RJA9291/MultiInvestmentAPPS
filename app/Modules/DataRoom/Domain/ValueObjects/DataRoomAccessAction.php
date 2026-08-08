<?php

namespace App\Modules\DataRoom\Domain\ValueObjects;

/** Matches DB-009's `action` column values and EVT-024/EVT-025. */
enum DataRoomAccessAction: string
{
    case Viewed = 'viewed';
    case Downloaded = 'downloaded';
}
