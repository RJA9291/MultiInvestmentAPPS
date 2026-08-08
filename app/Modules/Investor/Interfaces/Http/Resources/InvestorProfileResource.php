<?php

namespace App\Modules\Investor\Interfaces\Http\Resources;

use App\Modules\Investor\Domain\Entities\InvestorProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin InvestorProfile */
class InvestorProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var InvestorProfile $profile */
        $profile = $this->resource;

        return [
            'id' => $profile->id(),
            'investor_user_id' => $profile->investorUserId(),
            'investor_type' => $profile->investorType()->value,
            'verification_status' => $profile->verificationStatus()->value,
            'company_name' => $profile->companyName(),
            'investment_range' => $profile->investmentRange(),
            'preferred_industry' => $profile->preferredIndustry(),
            'risk_appetite' => $profile->riskAppetite(),
            'verified_by' => $profile->verifiedBy(),
            'verified_at' => $profile->verifiedAt()?->toIso8601String(),
        ];
    }
}
