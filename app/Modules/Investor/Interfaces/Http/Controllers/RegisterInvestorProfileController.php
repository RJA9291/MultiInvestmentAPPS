<?php

namespace App\Modules\Investor\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Investor\Application\Services\RegisterInvestorProfileService;
use App\Modules\Investor\Domain\ValueObjects\InvestorType;
use App\Modules\Investor\Interfaces\Http\Requests\RegisterInvestorProfileRequest;
use App\Modules\Investor\Interfaces\Http\Resources\InvestorProfileResource;
use DomainException;

/** Proposed API-022: POST /v1/investors/register */
class RegisterInvestorProfileController
{
    public function __invoke(RegisterInvestorProfileRequest $request, RegisterInvestorProfileService $service)
    {
        $validated = $request->validated();

        try {
            $profile = $service->execute(
                investorUserId: $validated['investor_user_id'],
                investorType: InvestorType::from($validated['investor_type']),
                companyName: $validated['company_name'] ?? null,
                investmentRange: $validated['investment_range'] ?? null,
                preferredIndustry: $validated['preferred_industry'] ?? null,
                riskAppetite: $validated['risk_appetite'] ?? null,
            );
        } catch (DomainException $e) {
            return ApiResponse::error('INVESTOR_PROFILE_ALREADY_EXISTS', $e->getMessage(), status: 409);
        }

        return ApiResponse::success(new InvestorProfileResource($profile), status: 201);
    }
}
