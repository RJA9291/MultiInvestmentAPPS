<?php

namespace App\Modules\Notification\Domain\Policies;

/**
 * UserScopingPolicy (BR-098, 06_DOMAIN_MODEL.md §8) — "only relevant user"
 * (Project Owner's Security Rules §10, WAJIB). Structurally enforced by
 * `SendNotificationService::execute()` only ever accepting ONE
 * `recipientUserId` (never an array/broadcast list) — this Policy is the
 * explicit guard that a blank/placeholder recipient can never slip through,
 * since that would otherwise be indistinguishable from "send to everyone."
 */
class UserScopingPolicy
{
    public function isValidRecipient(?string $recipientUserId): bool
    {
        return is_string($recipientUserId) && trim($recipientUserId) !== '';
    }
}
