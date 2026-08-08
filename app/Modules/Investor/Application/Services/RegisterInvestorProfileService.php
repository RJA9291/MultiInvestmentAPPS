<?php

namespace App\Modules\Investor\Application\Services;

use App\Modules\Investor\Domain\Entities\InvestorProfile;
use App\Modules\Investor\Domain\Events\InvestorProfileRegistered;
use App\Modules\Investor\Domain\Repositories\InvestorProfileRepositoryInterface;
use App\Modules\Investor\Domain\ValueObjects\InvestorType;
use DomainException;
use Illuminate\Support\Str;

/**
 * RegisterInvestorProfileService — proposed API-022.
 *
 * NOT "Investor Register" in the Identity sense (email/password account
 * creation) — that is Identity Context's User Aggregate, not yet built in
 * this codebase (same flagged gap as every other `*_user_id` field this
 * Sprint). This Service assumes `investorUserId` already refers to an
 * existing account and creates the investment-specific profile extension
 * only. See investor_profiles migration's doc comment for the full
 * reconciliation against the Project Owner's sketch.
 */
class RegisterInvestorProfileService
{
    public function __construct(private readonly InvestorProfileRepositoryInterface $profiles)
    {
    }

    public function execute(
        string $investorUserId,
        InvestorType $investorType,
        ?string $companyName = null,
        ?string $investmentRange = null,
        ?string $preferredIndustry = null,
        ?string $riskAppetite = null,
    ): InvestorProfile {
        if ($this->profiles->findByInvestorUserId($investorUserId)) {
            throw new DomainException("An investor profile already exists for user {$investorUserId}.");
        }

        $profile = InvestorProfile::register(
            id: (string) Str::uuid(),
            investorUserId: $investorUserId,
            investorType: $investorType,
            companyName: $companyName,
            investmentRange: $investmentRange,
            preferredIndustry: $preferredIndustry,
            riskAppetite: $riskAppetite,
        );

        $this->profiles->save($profile);

        InvestorProfileRegistered::dispatch($profile->id(), $investorUserId);

        return $profile;
    }
}
