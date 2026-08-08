<?php

namespace App\Modules\Notification\Domain\Repositories;

use App\Modules\Notification\Domain\Entities\Notification;

interface NotificationRepositoryInterface
{
    public function find(string $id): ?Notification;

    /** @return array<int, Notification> */
    public function findForRecipient(string $recipientUserId): array;

    public function save(Notification $notification): void;
}
