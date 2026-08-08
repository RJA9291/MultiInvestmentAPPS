<?php

namespace App\Modules\DataRoom\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\DataRoom\Application\Services\GrantDataRoomAccessService;
use App\Modules\DataRoom\Domain\ValueObjects\PermissionTier;
use App\Modules\DataRoom\Interfaces\Http\Requests\GrantDataRoomAccessRequest;
use App\Modules\DataRoom\Interfaces\Http\Resources\DataRoomGrantResource;
use Carbon\Carbon;
use DomainException;

/** API-005: POST /v1/data-room-grants */
class GrantDataRoomAccessController
{
    public function __invoke(GrantDataRoomAccessRequest $request, GrantDataRoomAccessService $service)
    {
        $validated = $request->validated();

        try {
            $grant = $service->execute(
                documentId: $validated['document_id'],
                granteeUserId: $validated['grantee_user_id'],
                grantedByUserId: $validated['granted_by_user_id'],
                grantedByUserRole: $validated['granted_by_user_role'],
                permissionTier: isset($validated['permission_tier'])
                    ? PermissionTier::from($validated['permission_tier'])
                    : PermissionTier::ViewOnly,
                expiresAt: isset($validated['expires_at']) ? Carbon::parse($validated['expires_at']) : null,
            );
        } catch (DomainException $e) {
            return ApiResponse::error('DATA_ROOM_GRANT_NOT_AUTHORIZED', $e->getMessage(), status: 403);
        }

        return ApiResponse::success(new DataRoomGrantResource($grant), status: 201);
    }
}
