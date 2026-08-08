<?php

namespace App\Modules\Investor\Application\Services;

use App\Modules\Investor\Domain\Entities\InvestorProfile;
use App\Modules\Investor\Domain\Events\InvestorRejected;
use App\Modules\Investor\Domain\Events\InvestorVerified;
use App\Modules\Investor\Domain\Repositories\InvestorProfileRepositoryInterface;
use RuntimeException;

/** InvestorVerificationService — proposed API-023 (verify/reject, Admin/Compliance). */
class InvestorVerificationService
{
    public function __construct(private readonly InvestorProfileRepositoryInterface $profiles)
    {
    }

    public function verify(string $investorProfileId, string $verifiedBy): InvestorProfile
    {
        $profile = $this->find($investorProfileId);

        $profile->verify($verifiedBy);
        $this->profiles->save($profile);

        InvestorVerified::dispatch($profile->id(), $verifiedBy);

        return $profile;
    }

    public function reject(string $investorProfileId, string $rejectedBy): InvestorProfile
    {
        $profile = $this->find($investorProfileId);

        $profile->reject($rejectedBy);
        $this->profiles->save($profile);

        InvestorRejected::dispatch($profile->id(), $rejectedBy);

        return $profile;
    }

    private function find(string $investorProfileId): InvestorProfile
    {
        $profile = $this->profiles->find($investorProfileId);

        if (! $profile) {
            throw new RuntimeException("Investor profile {$investorProfileId} not found.");
        }

        return $profile;
    }
}
