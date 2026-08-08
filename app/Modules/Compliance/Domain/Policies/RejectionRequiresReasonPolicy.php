<?php

namespace App\Modules\Compliance\Domain\Policies;

/** RejectionRequiresReasonPolicy (BR-141) — a rejection without >= 1 comment cannot be saved. */
class RejectionRequiresReasonPolicy
{
    public function isSatisfied(array $comments): bool
    {
        return count($comments) >= 1;
    }
}
