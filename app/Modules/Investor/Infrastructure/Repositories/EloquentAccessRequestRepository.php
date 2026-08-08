<?php

namespace App\Modules\Investor\Infrastructure\Repositories;

use App\Modules\Investor\Domain\Entities\AccessRequest;
use App\Modules\Investor\Domain\Repositories\AccessRequestRepositoryInterface;
use App\Modules\Investor\Infrastructure\Eloquent\AccessRequestModel;
use App\Modules\Investor\Infrastructure\Mappers\AccessRequestMapper;

class EloquentAccessRequestRepository implements AccessRequestRepositoryInterface
{
    public function __construct(private readonly AccessRequestMapper $mapper)
    {
    }

    public function find(string $id): ?AccessRequest
    {
        $model = AccessRequestModel::find($id);

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findByProjectId(string $projectId): array
    {
        return AccessRequestModel::where('project_id', $projectId)
            ->get()
            ->map(fn (AccessRequestModel $model) => $this->mapper->toDomain($model))
            ->all();
    }

    public function save(AccessRequest $request): void
    {
        $existing = AccessRequestModel::find($request->id());
        $this->mapper->toModel($request, $existing)->save();
    }

    public function countAll(): int
    {
        return AccessRequestModel::count();
    }

    public function countByStatus(string $status): int
    {
        return AccessRequestModel::where('status', $status)->count();
    }
}
