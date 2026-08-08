<?php

namespace App\Modules\DataRoom\Infrastructure\Repositories;

use App\Modules\DataRoom\Domain\Repositories\DataRoomAccessLogRepositoryInterface;
use App\Modules\DataRoom\Domain\ValueObjects\DataRoomAccessAction;
use App\Modules\DataRoom\Infrastructure\Eloquent\DataRoomAccessLogModel;

/**
 * The only code path allowed to write `data_room_access_logs` — matches
 * this Sprint's Repository-pattern discipline (14_LARAVEL_BLUEPRINT.md §9's
 * explicit "Direct access DB tanpa Repository" prohibition), replacing the
 * Project Owner's raw DB::table()->insert() sketch.
 */
class EloquentDataRoomAccessLogRepository implements DataRoomAccessLogRepositoryInterface
{
    public function record(string $dataRoomGrantId, DataRoomAccessAction $action, ?string $ipAddress): void
    {
        // `id` is deliberately not in $fillable (same reasoning as every
        // other Model this Sprint) — HasUuids generates it automatically on
        // the model's "creating" event, so it must never be passed here.
        DataRoomAccessLogModel::create([
            'data_room_grant_id' => $dataRoomGrantId,
            'action' => $action->value,
            'ip_address' => $ipAddress,
            'occurred_at' => now(),
        ]);
    }
}
