<?php

namespace App\Modules\Notification\Application\Contracts;

use App\Modules\Notification\Domain\Entities\Notification;

/**
 * EmailNotificationGatewayInterface — the port to Integration Context's
 * Email Adapter (06_DOMAIN_MODEL.md §13), which is NOT built in this
 * codebase yet (no verified transactional-email provider configured). This
 * interface exists so that binding a real implementation later is the only
 * change needed — same pattern as AiProviderGatewayInterface's
 * NullAiProviderGateway default.
 */
interface EmailNotificationGatewayInterface
{
    public function send(string $recipientUserId, Notification $notification): void;
}
