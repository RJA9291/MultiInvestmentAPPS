<?php

namespace App\Modules\Project\Infrastructure\Mappers;

use App\Modules\Project\Domain\Entities\Project;
use App\Modules\Project\Domain\ValueObjects\PublishState;
use App\Modules\Project\Infrastructure\Eloquent\ProjectModel;

class ProjectMapper
{
    public function toDomain(ProjectModel $model): Project
    {
        return Project::reconstitute(
            id: $model->id,
            projectCode: $model->project_code,
            ownerUserId: $model->owner_user_id,
            title: $model->title,
            description: $model->description,
            category: $model->category,
            status: PublishState::from($model->status),
            currentComplianceReviewId: $model->current_compliance_review_id,
        );
    }

    /**
     * `id` is deliberately NOT in ProjectModel::$fillable (mass-assigning a
     * primary key is a common source of bugs) — it is set directly here
     * instead, only when creating a brand-new row.
     */
    public function toModel(Project $project, ?ProjectModel $existing = null): ProjectModel
    {
        $model = $existing ?? new ProjectModel();

        if (! $existing) {
            $model->id = $project->id();
        }

        $model->project_code = $project->projectCode();
        $model->owner_user_id = $project->ownerUserId();
        $model->title = $project->title();
        $model->description = $project->description();
        $model->category = $project->category();
        $model->status = $project->status()->value;
        $model->current_compliance_review_id = $project->currentComplianceReviewId();

        return $model;
    }
}
