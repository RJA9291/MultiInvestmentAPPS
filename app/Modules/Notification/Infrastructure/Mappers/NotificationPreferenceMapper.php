<?php

namespace App\Modules\Notification\Infrastructure\Mappers;

use App\Modules\Notification\Domain\Entities\NotificationPreference;
use App\Modules\Notification\Domain\ValueObjects\DeliveryChannel;
use App\Modules\Notification\Domain\ValueObjects\NotificationType;
use App\Modules\Notification\Infrastructure\Eloquent\NotificationPreferenceModel;

class NotificationPreferenceMapper
{
    public function toDomain(NotificationPreferenceModel $model): NotificationPreference
    {
        return NotificationPreference::reconstitute(
            id: $model->id,
            userId: $model->user_id,
            notificationType: NotificationType::from($model->notification_type),
            channel: DeliveryChannel::from($model->channel),
            isEnabled: $model->is_enabled,
        );
    }

    public function toModel(NotificationPreference $preference, ?NotificationPreferenceModel $existing = null): NotificationPreferenceModel
    {
        $model = $existing ?? new NotificationPreferenceModel();

        if (! $existing) {
            $model->id = $preference->id();
        }

        $model->fill([
            'user_id' => $preference->userId(),
            'notification_type' => $preference->notificationType()->value,
            'channel' => $preference->channel()->value,
            'is_enabled' => $preference->isEnabled(),
        ]);

        return $model;
    }
}
