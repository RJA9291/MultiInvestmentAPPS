<?php

namespace App\Modules\Notification\Application\Services;

use App\Modules\Notification\Application\Contracts\EmailNotificationGatewayInterface;
use App\Modules\Notification\Domain\Entities\Notification;
use App\Modules\Notification\Domain\Events\NotificationDelivered;
use App\Modules\Notification\Domain\Events\NotificationQueued;
use App\Modules\Notification\Domain\Policies\NoFundReferencePolicy;
use App\Modules\Notification\Domain\Policies\UserScopingPolicy;
use App\Modules\Notification\Domain\Repositories\NotificationPreferenceRepositoryInterface;
use App\Modules\Notification\Domain\Repositories\NotificationRepositoryInterface;
use App\Modules\Notification\Domain\ValueObjects\DeliveryChannel;
use App\Modules\Notification\Domain\ValueObjects\NotificationType;
use DomainException;
use Illuminate\Support\Str;

/**
 * SendNotificationService — replaces the Project Owner's sketch's raw
 * `DB::table('notifications')->insert()` with the Repository pattern
 * established for every Module this Sprint (14_LARAVEL_BLUEPRINT.md §9).
 *
 * Two content/recipient gates run BEFORE anything is persisted, per the
 * Project Owner's own Security Rules (§10): `UserScopingPolicy` (BR-098 —
 * "only relevant user," a single, non-empty recipient, never a broadcast)
 * and `NoFundReferencePolicy` (BR-097 — "expose sensitive data" guard).
 * Either failing throws rather than silently degrading, since a
 * mis-addressed or non-compliant notification is a real defect, not a
 * fallback-safe AI-style failure mode.
 */
class SendNotificationService
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notifications,
        private readonly NotificationPreferenceRepositoryInterface $preferences,
        private readonly EmailNotificationGatewayInterface $emailGateway,
        private readonly UserScopingPolicy $userScopingPolicy,
        private readonly NoFundReferencePolicy $noFundReferencePolicy,
    ) {
    }

    public function execute(
        string $recipientUserId,
        NotificationType $notificationType,
        string $title,
        string $message,
        array $metadata = [],
    ): Notification {
        if (! $this->userScopingPolicy->isValidRecipient($recipientUserId)) {
            throw new DomainException('Notification recipient must be a single, non-empty user id (BR-098).');
        }

        if (! $this->noFundReferencePolicy->isCompliant($title, $message)) {
            throw new DomainException('Notification content references fund/payment terms, which is not permitted (BR-097).');
        }

        $notification = Notification::queue(
            id: (string) Str::uuid(),
            recipientUserId: $recipientUserId,
            notificationType: $notificationType,
            title: $title,
            message: $message,
            metadata: $metadata,
        );

        $this->notifications->save($notification);
        NotificationQueued::dispatch($notification->id(), $recipientUserId, $notificationType->value);

        // In-app "delivery" is simply the row existing and being queryable —
        // no separate channel dispatch step, unlike email below.
        $notification->markDelivered();
        $this->notifications->save($notification);
        NotificationDelivered::dispatch($notification->id(), DeliveryChannel::InApp->value, $notification->deliveredAt()->toIso8601String());

        $this->maybeSendEmail($recipientUserId, $notificationType, $notification);

        return $notification;
    }

    /**
     * Email is opt-IN: only sent if a `notification_preferences` row exists
     * for this (user, type, email) triple AND is enabled. No row = no
     * email — a conservative default, since DB-029's own spec doesn't state
     * a default direction and fabricating "email on by default" would risk
     * sending to a user who never asked for it.
     */
    private function maybeSendEmail(string $recipientUserId, NotificationType $notificationType, Notification $notification): void
    {
        $preference = $this->preferences->findForUserTypeChannel($recipientUserId, $notificationType, DeliveryChannel::Email);

        if ($preference && $preference->isEnabled()) {
            $this->emailGateway->send($recipientUserId, $notification);
        }
    }
}
