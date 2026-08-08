<?php

namespace App\Modules\DataRoom\Domain\Policies;

/**
 * AdminNeverBypassesNdaPolicy (BR-047, 06_DOMAIN_MODEL.md §7 — shared with
 * Administration Context's AdminAccessGrant)
 *
 * "A Platform Admin may not bypass NDA gating to view a Data Room document
 * except through a separate, explicitly logged support-exception process."
 * Completely absent from the Project Owner's Data-Room brief — this is
 * already a locked cross-context rule (also referenced from
 * 06_DOMAIN_MODEL.md's Administration Context §7) and is enforced here
 * rather than silently dropped.
 *
 * FLAGGED: the "separate, explicitly logged support-exception process"
 * itself (Administration Context's AdminAccessGrant Aggregate) has not been
 * built in this Sprint — so in practice, isExempt() always returns false
 * today, meaning an Admin has NO path around NDA gating at all yet. That is
 * the correct, conservative default until AdminAccessGrant exists; it must
 * never be hand-waved to `true` to "unblock" an Admin workflow.
 */
class AdminNeverBypassesNdaPolicy
{
    public function isExemptFromNdaGating(string $actingUserRole, bool $hasLoggedSupportException): bool
    {
        if ($actingUserRole !== 'platform_admin') {
            return false; // this Policy only concerns Admin; other roles are governed by normal NDA gating
        }

        return $hasLoggedSupportException;
    }
}
