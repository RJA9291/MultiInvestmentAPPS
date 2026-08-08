<?php

namespace App\Modules\Investor\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Investor\Application\Services\ApproveAccessRequestService;
use App\Modules\Investor\Interfaces\Http\Requests\DecideAccessRequestRequest;
use App\Modules\Investor\Interfaces\Http\Resources\AccessRequestResource;
use DomainException;
use RuntimeException;

/** Proposed API-026: POST /v1/access-requests/{id}/approve */
class ApproveAccessRequestController
{
    public function __invoke(string $id, DecideAccessRequestRequest $request, ApproveAccessRequestService $service)
    {
        $validated = $request->validated();

        try {
            $accessRequest = $service->execute($id, $validated['decided_by_user_id'], $validated['decided_by_user_role']);
        } catch (RuntimeException $e) {
            return ApiResponse::error('ACCESS_REQUEST_NOT_FOUND', $e->getMessage(), status: 404);
        } catch (DomainException $e) {
            return ApiResponse::error('ACCESS_REQUEST_ALREADY_DECIDED', $e->getMessage(), status: 409);
        }

        return ApiResponse::success(new AccessRequestResource($accessRequest));
    }
}
