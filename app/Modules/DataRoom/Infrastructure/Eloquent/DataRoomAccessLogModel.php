<?php

namespace App\Modules\DataRoom\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/** Eloquent model for `data_room_access_logs` (DB-009). Append-only — no updated_at. */
class DataRoomAccessLogModel extends Model
{
    use HasUuids;

    protected $table = 'data_room_access_logs';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'data_room_grant_id',
        'action',
        'ip_address',
        'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];
}
