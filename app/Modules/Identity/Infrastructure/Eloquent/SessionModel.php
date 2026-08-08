<?php

namespace App\Modules\Identity\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * DB-003: sessions. No Soft Delete per the locked design — an
 * expired/logged-out session is left with `ended_at` populated, not
 * deleted, for a short investigative window (90-day operational purge,
 * not built this Sprint).
 */
class SessionModel extends Model
{
    use HasUuids;

    public const UPDATED_AT = null;

    protected $table = 'sessions';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'token_hash',
        'expires_at',
        'ip_address',
        'ended_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'ended_at' => 'datetime',
    ];
}
