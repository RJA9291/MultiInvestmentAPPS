<?php

namespace App\Modules\Project\Domain\ValueObjects;

/**
 * PublishState (06_DOMAIN_MODEL.md §3.1, standardized lifecycle, §18)
 *
 * Draft -> Submitted -> UnderComplianceReview -> Approved -> Published -> Archived
 * plus Unpublished (manual, reversible, only from Published)
 * plus the rejection branch: UnderComplianceReview -> ReturnedToBusinessOwner
 * -> (Business Owner edits) -> Submitted (new review cycle, PDL-027).
 */
enum PublishState: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderComplianceReview = 'under_compliance_review';
    case Approved = 'approved';
    case Published = 'published';
    case Unpublished = 'unpublished';
    case Archived = 'archived';
    case ReturnedToBusinessOwner = 'returned_to_business_owner';

    /**
     * Valid forward transitions, per the standardized lifecycle
     * (06_DOMAIN_MODEL.md §18). This is the single source of truth the
     * Project Entity's transition methods check against — never duplicated
     * ad hoc inside a Service.
     */
    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Draft => $target === self::Submitted,
            self::Submitted => $target === self::UnderComplianceReview,
            self::UnderComplianceReview => in_array($target, [self::Approved, self::ReturnedToBusinessOwner], true),
            self::Approved => $target === self::Published,
            self::Published => in_array($target, [self::Unpublished, self::Archived], true),
            self::Unpublished => in_array($target, [self::Published, self::Archived], true),
            self::ReturnedToBusinessOwner => $target === self::Submitted, // ProjectResubmitted
            self::Archived => false, // terminal
        };
    }
}
