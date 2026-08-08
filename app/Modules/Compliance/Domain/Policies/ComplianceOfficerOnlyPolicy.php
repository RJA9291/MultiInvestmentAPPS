<?php

namespace App\Modules\Compliance\Domain\Policies;

/**
 * ComplianceOfficerOnlyPolicy (BR-139, 06_DOMAIN_MODEL.md §10)
 *
 * Only the Compliance Officer role may record an Approved/Rejected decision.
 * Enforced via AuthorizationGate in the full architecture (06_DOMAIN_MODEL.md
 * §2) — Identity Context / RBAC is NOT YET IMPLEMENTED in this pass, so this
 * Policy currently validates an explicit role string passed by the caller.
 * FLAGGED: wiring this to real authentication is a follow-up once the
 * Identity Module is built — do not treat this as full authorization.
 */
class ComplianceOfficerOnlyPolicy
{
    public function isAuthorized(string $actingUserRole): bool
    {
        return $actingUserRole === 'compliance_officer';
    }
}
