<?php

namespace App\Modules\DataRoom\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/** Eloquent model for `data_room_grants` (DB-008). Deliberately NO SoftDeletes — see migration doc comment. */
class DataRoomGrantModel extends Model
{
    use HasUuids;

    protected $table = 'data_room_grants';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'document_id',
        'grantee_user_id',
        'permission_tier',
        'granted_by',
        'granted_at',
        'revoked_by',
        'revoked_at',
        'expires_at',
    ];

    protected $casts = [
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}
