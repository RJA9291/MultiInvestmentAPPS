<?php

namespace App\Modules\DataRoom\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\DataRoom\Application\Services\RevokeDataRoomAccessService;
use App\Modules\DataRoom\Interfaces\Http\Requests\RevokeDataRoomAccessRequest;
use RuntimeException;

/** Proposed API-013: PATCH /v1/data-room-grants/{id}/revoke */
class RevokeDataRoomAccessController
{
    public function __invoke(string $id, RevokeDataRoomAccessRequest $request, RevokeDataRoomAccessService $service)
    {
        try {
            $service->execute($id, $request->validated()['revoked_by_user_id']);
        } catch (RuntimeException $e) {
            return ApiResponse::error('DATA_ROOM_GRANT_NOT_FOUND', $e->getMessage(), status: 404);
        }

        return ApiResponse::success(['revoked' => true]);
    }
}
