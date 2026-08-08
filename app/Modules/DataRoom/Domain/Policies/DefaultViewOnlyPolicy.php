<?php

namespace App\Modules\DataRoom\Domain\Policies;

use App\Modules\DataRoom\Domain\ValueObjects\PermissionTier;

/**
 * DefaultViewOnlyPolicy (BR-041)
 *
 * "A Data Room document's default permission tier is view-only unless the
 * Business Owner explicitly sets it to downloadable." DataRoomGrant::grant()
 * already defaults its $permissionTier parameter to ViewOnly, which
 * satisfies the "default" half of BR-041 structurally. This Policy is the
 * other half: guarding that only a Business Owner may request Downloadable.
 *
 * FLAGGED: real RBAC pending Identity Module, same caveat as
 * ComplianceOfficerOnlyPolicy — this Policy validates an explicit role
 * string passed by the caller, not real authentication.
 */
class DefaultViewOnlyPolicy
{
    public function isAuthorizedToGrant(PermissionTier $tier, string $actingUserRole): bool
    {
        if ($tier === PermissionTier::ViewOnly) {
            return true;
        }

        return $actingUserRole === 'business_owner';
    }
}
