<?php

namespace App\Modules\Notification\Infrastructure\Gateway;

use App\Modules\Notification\Application\Contracts\EmailNotificationGatewayInterface;
use App\Modules\Notification\Domain\Entities\Notification;
use Illuminate\Support\Facades\Log;

/**
 * LogOnlyEmailNotificationGateway — the honest default binding.
 *
 * The Project Owner's brief marked email as "optional now, ready for
 * later" and sketched `Mail::to($user->email)->send(new NotificationMail($data))`
 * directly inside the Service. Two problems with building that literally:
 * (1) it would hard-code a Laravel `Mail` facade call into the Application
 * layer instead of going through a port, making a future real provider swap
 * touch this Service instead of only a binding; (2) `$user->email` requires
 * loading a User from Identity Context, which — same flagged gap as every
 * other Module this Sprint — is not built yet, so there's no `$user` object
 * to read an email address from at all.
 *
 * This gateway logs the send intent instead of fabricating a delivery that
 * cannot actually happen — the same "never invent an unverified capability"
 * discipline already applied to `NullAiProviderGateway`/`WatermarkService`.
 */
class LogOnlyEmailNotificationGateway implements EmailNotificationGatewayInterface
{
    public function send(string $recipientUserId, Notification $notification): void
    {
        Log::info('LogOnlyEmailNotificationGateway: email send skipped (no Email Adapter configured).', [
            'recipient_user_id' => $recipientUserId,
            'notification_id' => $notification->id(),
            'notification_type' => $notification->notificationType()->value,
        ]);
    }
}
