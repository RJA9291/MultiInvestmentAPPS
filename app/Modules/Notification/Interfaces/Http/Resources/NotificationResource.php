<?php

namespace App\Modules\Notification\Interfaces\Http\Resources;

use App\Modules\Notification\Domain\Entities\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Notification */
class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Notification $notification */
        $notification = $this->resource;

        return [
            'id' => $notification->id(),
            'recipient_user_id' => $notification->recipientUserId(),
            'notification_type' => $notification->notificationType()->value,
            'title' => $notification->title(),
            'message' => $notification->message(),
            'metadata' => $notification->metadata(),
            'is_read' => $notification->isRead(),
            'queued_at' => $notification->queuedAt()->toIso8601String(),
            'delivered_at' => $notification->deliveredAt()?->toIso8601String(),
            'read_at' => $notification->readAt()?->toIso8601String(),
        ];
    }
}
