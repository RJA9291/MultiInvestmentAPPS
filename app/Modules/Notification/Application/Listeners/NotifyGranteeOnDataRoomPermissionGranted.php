<?php

namespace App\Modules\Notification\Application\Listeners;

use App\Modules\DataRoom\Domain\Events\DataRoomPermissionGranted;
use App\Modules\Notification\Application\Services\SendNotificationService;
use App\Modules\Notification\Domain\ValueObjects\NotificationType;

/** Reacts to DataRoom Module's DataRoomPermissionGranted (EVT-021) — granteeUserId is directly in the payload. */
class NotifyGranteeOnDataRoomPermissionGranted
{
    public function __construct(private readonly SendNotificationService $notifications)
    {
    }

    public function handle(DataRoomPermissionGranted $event): void
    {
        $this->notifications->execute(
            recipientUserId: $event->granteeUserId,
            notificationType: NotificationType::DataRoomGranted,
            title: 'Data Room Access Granted',
            message: 'You have been granted access to a document in the data room.',
            metadata: ['document_id' => $event->documentId, 'grant_id' => $event->grantId],
        );
    }
}
