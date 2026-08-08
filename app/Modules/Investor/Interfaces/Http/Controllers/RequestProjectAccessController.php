<?php

namespace App\Modules\Investor\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Investor\Application\Services\RequestProjectAccessService;
use App\Modules\Investor\Interfaces\Http\Requests\RequestProjectAccessRequest;
use App\Modules\Investor\Interfaces\Http\Resources\AccessRequestResource;
use DomainException;
use RuntimeException;

/** Proposed API-025: POST /v1/projects/{id}/request-access */
class RequestProjectAccessController
{
    public function __invoke(string $id, RequestProjectAccessRequest $request, RequestProjectAccessService $service)
    {
        $validated = $request->validated();

        try {
            $accessRequest = $service->execute($id, $validated['investor_user_id']);
        } catch (RuntimeException $e) {
            return ApiResponse::error('PROJECT_NOT_FOUND', $e->getMessage(), status: 404);
        } catch (DomainException $e) {
            return ApiResponse::error('ACCESS_REQUEST_REJECTED', $e->getMessage(), status: 422);
        }

        return ApiResponse::success(new AccessRequestResource($accessRequest), status: 201);
    }
}
