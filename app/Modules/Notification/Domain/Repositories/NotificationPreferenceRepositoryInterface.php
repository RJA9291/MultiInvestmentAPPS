<?php

namespace App\Modules\Notification\Domain\Repositories;

use App\Modules\Notification\Domain\Entities\NotificationPreference;
use App\Modules\Notification\Domain\ValueObjects\DeliveryChannel;
use App\Modules\Notification\Domain\ValueObjects\NotificationType;

interface NotificationPreferenceRepositoryInterface
{
    public function findForUserTypeChannel(
        string $userId,
        NotificationType $notificationType,
        DeliveryChannel $channel,
    ): ?NotificationPreference;

    public function save(NotificationPreference $preference): void;
}
