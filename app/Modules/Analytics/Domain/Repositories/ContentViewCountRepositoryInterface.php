<?php

namespace App\Modules\Analytics\Domain\Repositories;

/**
 * Deliberately a thin interface (record + count only, no Entity, no
 * find()) — mirrors `DataRoomAccessLogRepositoryInterface`'s own precedent
 * of staying write-heavy/read-rarely without inventing query methods no
 * use case actually drives yet (DB-048's own Cache Strategy note).
 */
interface ContentViewCountRepositoryInterface
{
    /** Increments the view counter for a Project or Document, creating the row on first view. */
    public function recordView(string $viewableType, string $viewableId): void;

    public function countFor(string $viewableType, string $viewableId): int;
}
