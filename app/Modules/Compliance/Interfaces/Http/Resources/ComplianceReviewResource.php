<?php

namespace App\Modules\Compliance\Interfaces\Http\Resources;

use App\Modules\Compliance\Domain\Entities\ComplianceReview;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ComplianceReview */
class ComplianceReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ComplianceReview $review */
        $review = $this->resource;

        return [
            'id' => $review->id(),
            'project_id' => $review->projectId(),
            'cycle_number' => $review->cycleNumber(),
            'reviewer_user_id' => $review->reviewerUserId(),
            'status' => $review->status()->value,
            'decision_made_by' => $review->decisionMadeBy(), // PDL-059
            'decision_source' => $review->decisionSource(), // PDL-059, 'HUMAN' | 'AI_ASSISTED', provenance only
        ];
    }
}
