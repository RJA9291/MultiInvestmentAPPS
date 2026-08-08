<?php

namespace App\Modules\Notification\Infrastructure\Repositories;

use App\Modules\Notification\Domain\Entities\Notification;
use App\Modules\Notification\Domain\Repositories\NotificationRepositoryInterface;
use App\Modules\Notification\Infrastructure\Eloquent\NotificationModel;
use App\Modules\Notification\Infrastructure\Mappers\NotificationMapper;

class EloquentNotificationRepository implements NotificationRepositoryInterface
{
    public function __construct(private readonly NotificationMapper $mapper)
    {
    }

    public function find(string $id): ?Notification
    {
        $model = NotificationModel::find($id);

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findForRecipient(string $recipientUserId): array
    {
        return NotificationModel::where('recipient_user_id', $recipientUserId)
            ->orderByDesc('queued_at')
            ->get()
            ->map(fn (NotificationModel $model) => $this->mapper->toDomain($model))
            ->all();
    }

    public function save(Notification $notification): void
    {
        $existing = NotificationModel::find($notification->id());
        $this->mapper->toModel($notification, $existing)->save();
    }
}
