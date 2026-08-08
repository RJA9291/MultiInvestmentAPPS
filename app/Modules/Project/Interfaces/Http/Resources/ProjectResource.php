<?php

namespace App\Modules\Project\Interfaces\Http\Resources;

use App\Modules\Project\Domain\Entities\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Project */
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Project $project */
        $project = $this->resource;

        return [
            'id' => $project->id(),
            'project_code' => $project->projectCode(),
            'owner_user_id' => $project->ownerUserId(),
            'title' => $project->title(),
            'description' => $project->description(),
            'category' => $project->category(),
            'status' => $project->status()->value,
            'current_compliance_review_id' => $project->currentComplianceReviewId(),
        ];
    }
}
