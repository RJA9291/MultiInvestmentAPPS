<?php

namespace App\Modules\Analytics\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ContentViewCountModel extends Model
{
    use HasUuids;

    protected $table = 'content_view_counts';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'viewable_type',
        'viewable_id',
        'view_count',
        'last_viewed_at',
    ];

    protected $casts = [
        'view_count' => 'integer',
        'last_viewed_at' => 'datetime',
    ];
}
