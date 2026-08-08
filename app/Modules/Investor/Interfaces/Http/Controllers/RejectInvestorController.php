<?php

namespace App\Modules\Investor\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Investor\Application\Services\InvestorVerificationService;
use App\Modules\Investor\Interfaces\Http\Requests\RejectInvestorRequest;
use App\Modules\Investor\Interfaces\Http\Resources\InvestorProfileResource;
use DomainException;
use RuntimeException;

/** Proposed API-023's reject counterpart: POST /v1/investors/{id}/reject */
class RejectInvestorController
{
    public function __invoke(string $id, RejectInvestorRequest $request, InvestorVerificationService $service)
    {
        $validated = $request->validated();

        try {
            $profile = $service->reject($id, $validated['rejected_by_user_id']);
        } catch (RuntimeException $e) {
            return ApiResponse::error('INVESTOR_PROFILE_NOT_FOUND', $e->getMessage(), status: 404);
        } catch (DomainException $e) {
            return ApiResponse::error('INVESTOR_ALREADY_DECIDED', $e->getMessage(), status: 409);
        }

        return ApiResponse::success(new InvestorProfileResource($profile));
    }
}
