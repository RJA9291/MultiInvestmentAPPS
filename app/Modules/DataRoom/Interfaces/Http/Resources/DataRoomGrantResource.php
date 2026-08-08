<?php

namespace App\Modules\DataRoom\Interfaces\Http\Resources;

use App\Modules\DataRoom\Domain\Entities\DataRoomGrant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin DataRoomGrant */
class DataRoomGrantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var DataRoomGrant $grant */
        $grant = $this->resource;

        return [
            'id' => $grant->id(),
            'document_id' => $grant->documentId(),
            'grantee_user_id' => $grant->granteeUserId(),
            'permission_tier' => $grant->permissionTier()->value,
            'granted_by' => $grant->grantedBy(),
            'granted_at' => $grant->grantedAt()->toIso8601String(),
            'revoked_by' => $grant->revokedBy(),
            'revoked_at' => $grant->revokedAt()?->toIso8601String(),
            'expires_at' => $grant->expiresAt()?->toIso8601String(),
            'is_active' => $grant->isActive(),
        ];
    }
}
