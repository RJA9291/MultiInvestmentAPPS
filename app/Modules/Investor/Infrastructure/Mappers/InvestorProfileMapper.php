<?php

namespace App\Modules\Investor\Infrastructure\Mappers;

use App\Modules\Investor\Domain\Entities\InvestorProfile;
use App\Modules\Investor\Domain\ValueObjects\InvestorType;
use App\Modules\Investor\Domain\ValueObjects\InvestorVerificationStatus;
use App\Modules\Investor\Infrastructure\Eloquent\InvestorProfileModel;

class InvestorProfileMapper
{
    public function toDomain(InvestorProfileModel $model): InvestorProfile
    {
        return InvestorProfile::reconstitute(
            id: $model->id,
            investorUserId: $model->investor_user_id,
            investorType: InvestorType::from($model->investor_type),
            verificationStatus: InvestorVerificationStatus::from($model->verification_status),
            companyName: $model->company_name,
            investmentRange: $model->investment_range,
            preferredIndustry: $model->preferred_industry,
            riskAppetite: $model->risk_appetite,
            verifiedBy: $model->verified_by,
            verifiedAt: $model->verified_at,
        );
    }

    /**
     * `id` is deliberately NOT in InvestorProfileModel::$fillable (mass-
     * assigning a primary key is a common source of bugs, and would throw
     * a MassAssignmentException here) — it is set directly as a property
     * instead, only when creating a brand-new row, mirroring ProjectMapper's
     * established pattern.
     */
    public function toModel(InvestorProfile $profile, ?InvestorProfileModel $existing = null): InvestorProfileModel
    {
        $model = $existing ?? new InvestorProfileModel();

        if (! $existing) {
            $model->id = $profile->id();
        }

        $model->fill([
            'investor_user_id' => $profile->investorUserId(),
            'investor_type' => $profile->investorType()->value,
            'verification_status' => $profile->verificationStatus()->value,
            'company_name' => $profile->companyName(),
            'investment_range' => $profile->investmentRange(),
            'preferred_industry' => $profile->preferredIndustry(),
            'risk_appetite' => $profile->riskAppetite(),
            'verified_by' => $profile->verifiedBy(),
            'verified_at' => $profile->verifiedAt(),
        ]);

        return $model;
    }
}
