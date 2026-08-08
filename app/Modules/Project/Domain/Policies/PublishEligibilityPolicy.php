<?php

namespace App\Modules\Project\Domain\Policies;

use App\Modules\Project\Domain\Entities\Project;
use App\Modules\Project\Domain\ValueObjects\PublishState;

/**
 * PublishEligibilityPolicy (BR-017, 06_DOMAIN_MODEL.md §3.1)
 *
 * A Project must have >= 1 approved Document, AND must be in Approved state
 * (per the standardized lifecycle) before it may transition to Published.
 *
 * WAJIB: this is a Domain Policy (business invariant, Domain layer) — NOT a
 * Laravel Authorization Policy (14_LARAVEL_BLUEPRINT.md §10, PDL-051). It is
 * never merged with, and never confused for, the framework's Gate-based
 * authorization class of a similar name.
 */
class PublishEligibilityPolicy
{
    /**
     * @param  int  $approvedDocumentCount  Injected by the calling Service —
     *              Document Module ownership is out of scope for this pass
     *              (14_LARAVEL_BLUEPRINT.md §1.1's split: Project/Document/
     *              DataRoom are separate Modules within one Bounded Context).
     */
    public function isEligible(Project $project, int $approvedDocumentCount): bool
    {
        if ($project->status() !== PublishState::Approved) {
            return false;
        }

        return $approvedDocumentCount >= 1;
    }
}
