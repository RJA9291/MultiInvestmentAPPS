<?php

namespace App\Modules\Investor\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AccessRequestModel extends Model
{
    use HasUuids;

    protected $table = 'access_requests';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'project_id',
        'investor_user_id',
        'status',
        'requested_at',
        'decided_by',
        'decided_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'decided_at' => 'datetime',
    ];
}
