<?php

namespace App\Modules\Notification\Infrastructure\Mappers;

use App\Modules\Notification\Domain\Entities\Notification;
use App\Modules\Notification\Domain\ValueObjects\NotificationType;
use App\Modules\Notification\Infrastructure\Eloquent\NotificationModel;

class NotificationMapper
{
    public function toDomain(NotificationModel $model): Notification
    {
        return Notification::reconstitute(
            id: $model->id,
            recipientUserId: $model->recipient_user_id,
            notificationType: NotificationType::from($model->notification_type),
            title: $model->title,
            message: $model->message,
            metadata: $model->metadata ?? [],
            isRead: $model->is_read,
            queuedAt: $model->queued_at,
            deliveredAt: $model->delivered_at,
            readAt: $model->read_at,
        );
    }

    /**
     * `id` is deliberately NOT in NotificationModel::$fillable — set
     * directly as a property instead, only when creating a brand-new row
     * (mirrors ProjectMapper's established pattern, avoiding the
     * MassAssignmentException this repeatedly caught earlier this Sprint).
     */
    public function toModel(Notification $notification, ?NotificationModel $existing = null): NotificationModel
    {
        $model = $existing ?? new NotificationModel();

        if (! $existing) {
            $model->id = $notification->id();
        }

        $model->fill([
            'recipient_user_id' => $notification->recipientUserId(),
            'notification_type' => $notification->notificationType()->value,
            'title' => $notification->title(),
            'message' => $notification->message(),
            'metadata' => $notification->metadata(),
            'is_read' => $notification->isRead(),
            'queued_at' => $notification->queuedAt(),
            'delivered_at' => $notification->deliveredAt(),
            'read_at' => $notification->readAt(),
        ]);

        return $model;
    }
}
