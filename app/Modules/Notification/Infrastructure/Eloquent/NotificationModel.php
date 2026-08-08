<?php

namespace App\Modules\Notification\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class NotificationModel extends Model
{
    use HasUuids;

    protected $table = 'notifications';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'recipient_user_id',
        'notification_type',
        'title',
        'message',
        'metadata',
        'is_read',
        'queued_at',
        'delivered_at',
        'read_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_read' => 'boolean',
        'queued_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];
}
