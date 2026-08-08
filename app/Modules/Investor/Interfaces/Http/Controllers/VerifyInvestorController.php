<?php

namespace App\Modules\Investor\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Investor\Application\Services\InvestorVerificationService;
use App\Modules\Investor\Interfaces\Http\Requests\VerifyInvestorRequest;
use App\Modules\Investor\Interfaces\Http\Resources\InvestorProfileResource;
use DomainException;
use RuntimeException;

/** Proposed API-023: POST /v1/investors/{id}/verify (Admin/Compliance) */
class VerifyInvestorController
{
    public function __invoke(string $id, VerifyInvestorRequest $request, InvestorVerificationService $service)
    {
        $validated = $request->validated();

        try {
            $profile = $service->verify($id, $validated['verified_by_user_id']);
        } catch (RuntimeException $e) {
            return ApiResponse::error('INVESTOR_PROFILE_NOT_FOUND', $e->getMessage(), status: 404);
        } catch (DomainException $e) {
            return ApiResponse::error('INVESTOR_ALREADY_DECIDED', $e->getMessage(), status: 409);
        }

        return ApiResponse::success(new InvestorProfileResource($profile));
    }
}
