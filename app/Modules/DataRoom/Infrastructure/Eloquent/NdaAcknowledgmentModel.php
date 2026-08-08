<?php

namespace App\Modules\DataRoom\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/** Eloquent model for `nda_acknowledgments` (DB-010). No updated_at — a proof record, never mutated. */
class NdaAcknowledgmentModel extends Model
{
    use HasUuids;

    protected $table = 'nda_acknowledgments';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'data_room_grant_id',
        'nda_version_hash',
        'acknowledged_at',
        'acknowledged_ip',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
    ];
}
