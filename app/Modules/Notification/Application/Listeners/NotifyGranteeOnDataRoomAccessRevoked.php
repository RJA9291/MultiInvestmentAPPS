<?php

namespace App\Modules\Notification\Application\Listeners;

use App\Modules\DataRoom\Domain\Events\DataRoomAccessRevoked;
use App\Modules\DataRoom\Domain\Repositories\DataRoomGrantRepositoryInterface;
use App\Modules\Notification\Application\Services\SendNotificationService;
use App\Modules\Notification\Domain\ValueObjects\NotificationType;

/**
 * Reacts to DataRoom Module's DataRoomAccessRevoked (EVT-022). Its payload
 * only carries `grantId`/`revokedBy` — NOT the grantee's user id (`revokedBy`
 * is who performed the revocation, not who lost access) — resolved here via
 * `DataRoomGrantRepositoryInterface::find()` (interface-only, PDL-020).
 */
class NotifyGranteeOnDataRoomAccessRevoked
{
    public function __construct(
        private readonly SendNotificationService $notifications,
        private readonly DataRoomGrantRepositoryInterface $grants,
    ) {
    }

    public function handle(DataRoomAccessRevoked $event): void
    {
        $grant = $this->grants->find($event->grantId);

        if (! $grant) {
            return; // grant gone — nothing to notify, no fabricated recipient
        }

        $this->notifications->execute(
            recipientUserId: $grant->granteeUserId(),
            notificationType: NotificationType::DataRoomRevoked,
            title: 'Data Room Access Revoked',
            message: 'Your access to a document in the data room has been revoked.',
            metadata: ['document_id' => $grant->documentId(), 'grant_id' => $event->grantId],
        );
    }
}
