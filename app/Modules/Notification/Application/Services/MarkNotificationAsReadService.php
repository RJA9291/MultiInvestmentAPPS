<?php

namespace App\Modules\Notification\Application\Services;

use App\Modules\Notification\Domain\Entities\Notification;
use App\Modules\Notification\Domain\Events\NotificationRead;
use App\Modules\Notification\Domain\Repositories\NotificationRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use RuntimeException;

/**
 * MarkNotificationAsReadService — proposed API-028.
 *
 * WAJIB (Project Owner's brief §10, "only relevant user"): only the
 * notification's own recipient may mark it read — enforced here via an
 * explicit ownership check, not merely by scoping a query.
 */
class MarkNotificationAsReadService
{
    public function __construct(private readonly NotificationRepositoryInterface $notifications)
    {
    }

    public function execute(string $notificationId, string $requestingUserId): Notification
    {
        $notification = $this->notifications->find($notificationId);

        if (! $notification) {
            throw new RuntimeException("Notification {$notificationId} not found.");
        }

        if ($notification->recipientUserId() !== $requestingUserId) {
            throw new AuthorizationException("Notification {$notificationId} does not belong to this user.");
        }

        $notification->markAsRead();
        $this->notifications->save($notification);

        NotificationRead::dispatch($notification->id(), $notification->readAt()->toIso8601String());

        return $notification;
    }
}
