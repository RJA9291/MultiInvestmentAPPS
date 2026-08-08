<?php

namespace App\Modules\Notification\Application\Listeners;

use App\Modules\Investor\Domain\Events\InvestorRejected;
use App\Modules\Investor\Domain\Events\InvestorVerified;
use App\Modules\Investor\Domain\Repositories\InvestorProfileRepositoryInterface;
use App\Modules\Notification\Application\Services\SendNotificationService;
use App\Modules\Notification\Domain\ValueObjects\NotificationType;

/**
 * Reacts to Investor Module's InvestorVerified/InvestorRejected. Their
 * payload only carries `investorProfileId`, not the recipient's user id
 * directly — resolved here via `InvestorProfileRepositoryInterface`
 * (interface-only cross-Module dependency, PDL-020), not a fabricated
 * lookup.
 */
class NotifyInvestorOnVerificationDecision
{
    public function __construct(
        private readonly SendNotificationService $notifications,
        private readonly InvestorProfileRepositoryInterface $investorProfiles,
    ) {
    }

    public function handleVerified(InvestorVerified $event): void
    {
        $profile = $this->investorProfiles->find($event->investorProfileId);

        if (! $profile) {
            return; // profile gone — nothing to notify, no fabricated recipient
        }

        $this->notifications->execute(
            recipientUserId: $profile->investorUserId(),
            notificationType: NotificationType::InvestorVerified,
            title: 'Investor Profile Verified',
            message: 'Your investor profile has been verified. You can now request access to published projects.',
            metadata: ['investor_profile_id' => $event->investorProfileId],
        );
    }

    public function handleRejected(InvestorRejected $event): void
    {
        $profile = $this->investorProfiles->find($event->investorProfileId);

        if (! $profile) {
            return;
        }

        $this->notifications->execute(
            recipientUserId: $profile->investorUserId(),
            notificationType: NotificationType::InvestorRejected,
            title: 'Investor Profile Not Verified',
            message: 'Your investor profile could not be verified.',
            metadata: ['investor_profile_id' => $event->investorProfileId],
        );
    }
}
