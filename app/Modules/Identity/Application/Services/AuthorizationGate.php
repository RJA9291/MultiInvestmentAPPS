<?php

namespace App\Modules\Identity\Application\Services;

use App\Modules\Identity\Domain\ValueObjects\RoleSet;

/**
 * AuthorizationGate (06_DOMAIN_MODEL.md §2 — "the cross-context interface
 * every other context calls into", BR-003: "Only an authenticated user may
 * access any project, document, Workspace, or AI capability").
 *
 * This Sprint wires it in exactly two places: (1) the `JwtAuthenticate`
 * middleware, applied to the whole `/v1` route group so BR-003 is enforced
 * platform-wide rather than per-Module, and (2) the `role:` middleware
 * alias below for the small number of endpoints that are role-gated (e.g.
 * a Compliance Officer's review-decision endpoints, BR-139).
 *
 * Retrofitting fine-grained per-endpoint role checks into the other 8
 * Modules' existing Controllers is explicitly NOT done in this pass — it
 * is a mechanical follow-up (adding `role:x` to specific routes in
 * routes/api.php), flagged rather than silently left inconsistent.
 */
class AuthorizationGate
{
    /**
     * @param string[] $userRoles
     * @param string[] $allowedRoles
     */
    public function allows(array $userRoles, array $allowedRoles): bool
    {
        if (in_array(RoleSet::ADMIN, $userRoles, true)) {
            return true;
        }

        foreach ($allowedRoles as $role) {
            if (in_array($role, $userRoles, true)) {
                return true;
            }
        }

        return false;
    }
}
