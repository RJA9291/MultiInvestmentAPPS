<?php

namespace App\Modules\Compliance\Infrastructure\Mappers;

use App\Modules\Compliance\Domain\Entities\ComplianceReview;
use App\Modules\Compliance\Domain\ValueObjects\ComplianceStatus;
use App\Modules\Compliance\Infrastructure\Eloquent\ComplianceReviewModel;

class ComplianceReviewMapper
{
    public function toDomain(ComplianceReviewModel $model): ComplianceReview
    {
        return ComplianceReview::reconstitute(
            id: $model->id,
            projectId: $model->project_id,
            cycleNumber: $model->cycle_number,
            reviewerUserId: $model->reviewer_user_id,
            status: ComplianceStatus::from($model->status),
            decisionMadeBy: $model->decision_made_by,
            decisionSource: $model->decision_source,
        );
    }

    public function toModel(ComplianceReview $review, ?ComplianceReviewModel $existing = null): ComplianceReviewModel
    {
        $model = $existing ?? new ComplianceReviewModel();

        if (! $existing) {
            $model->id = $review->id();
            $model->project_id = $review->projectId();
            $model->cycle_number = $review->cycleNumber();
        }

        $model->reviewer_user_id = $review->reviewerUserId();
        $model->status = $review->status()->value;
        $model->decision_made_by = $review->decisionMadeBy(); // PDL-059, was decided_by
        $model->decision_source = $review->decisionSource(); // PDL-059, 'HUMAN' | 'AI_ASSISTED', provenance only

        if ($review->status()->value !== 'pending' && ! $model->decided_at) {
            $model->decided_at = now();
        }

        return $model;
    }
}
