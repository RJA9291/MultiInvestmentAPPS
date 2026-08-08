<?php

namespace App\Modules\Notification\Application\Listeners;

use App\Modules\Investor\Domain\Events\AccessRequestApproved;
use App\Modules\Investor\Domain\Events\AccessRequestRejected;
use App\Modules\Notification\Application\Services\SendNotificationService;
use App\Modules\Notification\Domain\ValueObjects\NotificationType;

/**
 * NotifyInvestorOnAccessDecision — reacts to Investor Module's
 * AccessRequestApproved (EVT-071) / AccessRequestRejected (EVT-072). The
 * recipient (`investorUserId`) is directly available in both event
 * payloads, so this notification can be sent without needing any
 * not-yet-built Identity/role-lookup capability.
 */
class NotifyInvestorOnAccessDecision
{
    public function __construct(private readonly SendNotificationService $notifications)
    {
    }

    public function handleApproved(AccessRequestApproved $event): void
    {
        $this->notifications->execute(
            recipientUserId: $event->investorUserId,
            notificationType: NotificationType::AccessApproved,
            title: 'Access Approved',
            message: 'You can now view the project data room.',
            metadata: ['project_id' => $event->projectId, 'access_request_id' => $event->accessRequestId],
        );
    }

    public function handleRejected(AccessRequestRejected $event): void
    {
        $this->notifications->execute(
            recipientUserId: $event->investorUserId,
            notificationType: NotificationType::AccessRejected,
            title: 'Access Request Declined',
            message: 'Your request for data room access was not approved.',
            metadata: ['project_id' => $event->projectId, 'access_request_id' => $event->accessRequestId],
        );
    }
}
