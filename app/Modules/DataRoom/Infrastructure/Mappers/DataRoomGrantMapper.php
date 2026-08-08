<?php

namespace App\Modules\DataRoom\Infrastructure\Mappers;

use App\Modules\DataRoom\Domain\Entities\DataRoomGrant;
use App\Modules\DataRoom\Domain\ValueObjects\PermissionTier;
use App\Modules\DataRoom\Infrastructure\Eloquent\DataRoomGrantModel;

class DataRoomGrantMapper
{
    public function toDomain(DataRoomGrantModel $model): DataRoomGrant
    {
        return DataRoomGrant::reconstitute(
            id: $model->id,
            documentId: $model->document_id,
            granteeUserId: $model->grantee_user_id,
            permissionTier: PermissionTier::from($model->permission_tier),
            grantedBy: $model->granted_by,
            grantedAt: $model->granted_at,
            revokedBy: $model->revoked_by,
            revokedAt: $model->revoked_at,
            expiresAt: $model->expires_at,
        );
    }

    public function toModel(DataRoomGrant $grant, ?DataRoomGrantModel $existing = null): DataRoomGrantModel
    {
        $model = $existing ?? new DataRoomGrantModel();

        if (! $existing) {
            $model->id = $grant->id(); // deliberately not fillable — see ProjectMapper's precedent for why
            $model->document_id = $grant->documentId();
            $model->grantee_user_id = $grant->granteeUserId();
            $model->permission_tier = $grant->permissionTier()->value;
            $model->granted_by = $grant->grantedBy();
            $model->granted_at = $grant->grantedAt();
        }

        $model->revoked_by = $grant->revokedBy();
        $model->revoked_at = $grant->revokedAt();
        $model->expires_at = $grant->expiresAt();

        return $model;
    }
}
