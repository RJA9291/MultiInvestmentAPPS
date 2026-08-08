<?php

namespace App\Modules\Investor\Infrastructure\Mappers;

use App\Modules\Investor\Domain\Entities\AccessRequest;
use App\Modules\Investor\Domain\ValueObjects\AccessRequestStatus;
use App\Modules\Investor\Infrastructure\Eloquent\AccessRequestModel;

class AccessRequestMapper
{
    public function toDomain(AccessRequestModel $model): AccessRequest
    {
        return AccessRequest::reconstitute(
            id: $model->id,
            projectId: $model->project_id,
            investorUserId: $model->investor_user_id,
            status: AccessRequestStatus::from($model->status),
            requestedAt: $model->requested_at,
            decidedBy: $model->decided_by,
            decidedAt: $model->decided_at,
        );
    }

    /**
     * `id` is deliberately NOT in AccessRequestModel::$fillable — set
     * directly as a property instead, only when creating a brand-new row,
     * mirroring ProjectMapper's established pattern (see that class's doc
     * comment for the MassAssignmentException this avoids).
     */
    public function toModel(AccessRequest $request, ?AccessRequestModel $existing = null): AccessRequestModel
    {
        $model = $existing ?? new AccessRequestModel();

        if (! $existing) {
            $model->id = $request->id();
        }

        $model->fill([
            'project_id' => $request->projectId(),
            'investor_user_id' => $request->investorUserId(),
            'status' => $request->status()->value,
            'requested_at' => $request->requestedAt(),
            'decided_by' => $request->decidedBy(),
            'decided_at' => $request->decidedAt(),
        ]);

        return $model;
    }
}
