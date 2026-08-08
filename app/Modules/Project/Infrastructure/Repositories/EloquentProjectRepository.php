<?php

namespace App\Modules\Project\Infrastructure\Repositories;

use App\Modules\Project\Domain\Entities\Project;
use App\Modules\Project\Domain\Repositories\ProjectRepositoryInterface;
use App\Modules\Project\Infrastructure\Eloquent\ProjectModel;
use App\Modules\Project\Infrastructure\Mappers\ProjectMapper;

class EloquentProjectRepository implements ProjectRepositoryInterface
{
    public function __construct(private readonly ProjectMapper $mapper)
    {
    }

    public function find(string $id): ?Project
    {
        $model = ProjectModel::find($id);

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findByCode(string $projectCode): ?Project
    {
        $model = ProjectModel::where('project_code', $projectCode)->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findPublished(): array
    {
        return ProjectModel::where('status', 'published')
            ->get()
            ->map(fn (ProjectModel $model) => $this->mapper->toDomain($model))
            ->all();
    }

    public function countAll(): int
    {
        return ProjectModel::count();
    }

    public function countByStatuses(array $statuses): int
    {
        return ProjectModel::whereIn('status', $statuses)->count();
    }

    public function save(Project $project): void
    {
        $existing = ProjectModel::find($project->id());
        $model = $this->mapper->toModel($project, $existing);
        $model->save();
    }
}
