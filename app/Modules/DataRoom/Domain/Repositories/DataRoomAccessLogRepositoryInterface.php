<?php

namespace App\Modules\DataRoom\Domain\Repositories;

use App\Modules\DataRoom\Domain\ValueObjects\DataRoomAccessAction;

/**
 * Deliberately a thin, write-only interface (record() only, no find()) —
 * DB-009 is append-only and "write-heavy, read-rarely (only on audit
 * review)" per its own Cache Strategy note; no query methods are invented
 * here without an actual audit-review use case driving them.
 */
interface DataRoomAccessLogRepositoryInterface
{
    public function record(string $dataRoomGrantId, DataRoomAccessAction $action, ?string $ipAddress): void;
}
