<?php

namespace App\Modules\Notification;

use App\Modules\DataRoom\Domain\Events\DataRoomAccessRevoked;
use App\Modules\DataRoom\Domain\Events\DataRoomPermissionGranted;
use App\Modules\Investor\Domain\Events\AccessRequestApproved;
use App\Modules\Investor\Domain\Events\AccessRequestRejected;
use App\Modules\Investor\Domain\Events\InvestorRejected;
use App\Modules\Investor\Domain\Events\InvestorVerified;
use App\Modules\Notification\Application\Contracts\EmailNotificationGatewayInterface;
use App\Modules\Notification\Application\Listeners\NotifyGranteeOnDataRoomAccessRevoked;
use App\Modules\Notification\Application\Listeners\NotifyGranteeOnDataRoomPermissionGranted;
use App\Modules\Notification\Application\Listeners\NotifyInvestorOnAccessDecision;
use App\Modules\Notification\Application\Listeners\NotifyInvestorOnVerificationDecision;
use App\Modules\Notification\Domain\Repositories\NotificationPreferenceRepositoryInterface;
use App\Modules\Notification\Domain\Repositories\NotificationRepositoryInterface;
use App\Modules\Notification\Infrastructure\Gateway\LogOnlyEmailNotificationGateway;
use App\Modules\Notification\Infrastructure\Repositories\EloquentNotificationPreferenceRepository;
use App\Modules\Notification\Infrastructure\Repositories\EloquentNotificationRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Notification Module's own Service Provider (14_LARAVEL_BLUEPRINT.md §6).
 *
 * Notification Context's whole responsibility is "react to Domain Events
 * published by every other context" (06_DOMAIN_MODEL.md §8) — so, unlike
 * every other Module this Sprint, it is EXPECTED to listen to many other
 * Modules' events from its own ServiceProvider. This is not the "central
 * EventServiceProvider" PDL-048 avoids (that was about NOT collecting every
 * Module's OWN reactions into one shared file) — it is Notification
 * Module's own reactions, living in Notification Module's own file, exactly
 * like every other Module's cross-context listeners this Sprint.
 *
 * ============================================================================
 * SCOPE DECISION, stated up front: only events where the recipient user id
 * is DIRECTLY available — either in the event payload itself, or via an
 * already-built Repository lookup — are wired below. The Project Owner's
 * own event-mapping table (§5) also asked for:
 *   - ProjectSubmitted -> "Notify Compliance"
 *   - DocumentUploaded -> "Notify Reviewer"
 *   - AICompliancePrecheckCompleted -> "Notify Compliance"
 *   - AIDocumentVerificationCompleted -> "Notify Compliance"
 * All four are ROLE-BASED / BROADCAST notifications ("every Compliance
 * Officer," "the assigned reviewer") — resolving them requires querying
 * `users`/`user_roles` by role, an Identity Module capability that is not
 * built in this codebase (same flagged gap as every `*_user_id` field this
 * Sprint). Sending to a hard-coded placeholder user id would be worse than
 * not sending at all — it would silently notify the wrong person. This is
 * left as an explicit, tracked gap, not fabricated. `ComplianceReviewStarted`
 * (Compliance Module) and `DocumentAiReviewReady` (AI Module) already exist
 * as the correct signals for a future Notification listener to subscribe to
 * once role-based recipient resolution exists — see those events' own doc
 * comments, written for exactly this purpose.
 * ============================================================================
 *
 * WAJIB: register this provider in bootstrap/providers.php / config/app.php.
 */
class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NotificationRepositoryInterface::class, EloquentNotificationRepository::class);
        $this->app->bind(NotificationPreferenceRepositoryInterface::class, EloquentNotificationPreferenceRepository::class);
        $this->app->bind(EmailNotificationGatewayInterface::class, LogOnlyEmailNotificationGateway::class);
    }

    public function boot(): void
    {
        Event::listen(AccessRequestApproved::class, [NotifyInvestorOnAccessDecision::class, 'handleApproved']);
        Event::listen(AccessRequestRejected::class, [NotifyInvestorOnAccessDecision::class, 'handleRejected']);

        Event::listen(InvestorVerified::class, [NotifyInvestorOnVerificationDecision::class, 'handleVerified']);
        Event::listen(InvestorRejected::class, [NotifyInvestorOnVerificationDecision::class, 'handleRejected']);

        Event::listen(DataRoomPermissionGranted::class, NotifyGranteeOnDataRoomPermissionGranted::class);
        Event::listen(DataRoomAccessRevoked::class, NotifyGranteeOnDataRoomAccessRevoked::class);
    }
}
