<?php

namespace App\Modules\Notification\Infrastructure\Repositories;

use App\Modules\Notification\Domain\Entities\NotificationPreference;
use App\Modules\Notification\Domain\Repositories\NotificationPreferenceRepositoryInterface;
use App\Modules\Notification\Domain\ValueObjects\DeliveryChannel;
use App\Modules\Notification\Domain\ValueObjects\NotificationType;
use App\Modules\Notification\Infrastructure\Eloquent\NotificationPreferenceModel;
use App\Modules\Notification\Infrastructure\Mappers\NotificationPreferenceMapper;

class EloquentNotificationPreferenceRepository implements NotificationPreferenceRepositoryInterface
{
    public function __construct(private readonly NotificationPreferenceMapper $mapper)
    {
    }

    public function findForUserTypeChannel(
        string $userId,
        NotificationType $notificationType,
        DeliveryChannel $channel,
    ): ?NotificationPreference {
        $model = NotificationPreferenceModel::where('user_id', $userId)
            ->where('notification_type', $notificationType->value)
            ->where('channel', $channel->value)
            ->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function save(NotificationPreference $preference): void
    {
        $existing = NotificationPreferenceModel::where('user_id', $preference->userId())
            ->where('notification_type', $preference->notificationType()->value)
            ->where('channel', $preference->channel()->value)
            ->first();

        $this->mapper->toModel($preference, $existing)->save();
    }
}
