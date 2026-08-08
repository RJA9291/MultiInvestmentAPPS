<?php

namespace App\Modules\Investor\Domain\Entities;

use App\Modules\Investor\Domain\ValueObjects\InvestorType;
use App\Modules\Investor\Domain\ValueObjects\InvestorVerificationStatus;
use Carbon\CarbonInterface;
use DomainException;

/**
 * InvestorProfile Aggregate Root (Investment Context — new this Sprint,
 * see 06_DOMAIN_MODEL.md's §5.x revision for the formal write-up).
 *
 * Deliberately holds ONLY investment-specific extension data. Identity
 * itself (email, name, password, login) belongs to Identity Context's
 * User Aggregate (06_DOMAIN_MODEL.md §2) — not yet built in this codebase,
 * same flagged gap as every other Module's `*_user_id` fields this Sprint.
 * `investorUserId` is a plain UserReference-shaped UUID; this class never
 * stores or validates an email address itself.
 */
class InvestorProfile
{
    private function __construct(
        private readonly string $id,
        private readonly string $investorUserId,
        private readonly InvestorType $investorType,
        private InvestorVerificationStatus $verificationStatus,
        private ?string $companyName,
        private ?string $investmentRange,
        private ?string $preferredIndustry,
        private ?string $riskAppetite,
        private ?string $verifiedBy,
        private ?CarbonInterface $verifiedAt,
    ) {
    }

    public static function register(
        string $id,
        string $investorUserId,
        InvestorType $investorType,
        ?string $companyName = null,
        ?string $investmentRange = null,
        ?string $preferredIndustry = null,
        ?string $riskAppetite = null,
    ): self {
        return new self(
            $id,
            $investorUserId,
            $investorType,
            InvestorVerificationStatus::Pending,
            $companyName,
            $investmentRange,
            $preferredIndustry,
            $riskAppetite,
            null,
            null,
        );
    }

    public static function reconstitute(
        string $id,
        string $investorUserId,
        InvestorType $investorType,
        InvestorVerificationStatus $verificationStatus,
        ?string $companyName,
        ?string $investmentRange,
        ?string $preferredIndustry,
        ?string $riskAppetite,
        ?string $verifiedBy,
        ?CarbonInterface $verifiedAt,
    ): self {
        return new self(
            $id,
            $investorUserId,
            $investorType,
            $verificationStatus,
            $companyName,
            $investmentRange,
            $preferredIndustry,
            $riskAppetite,
            $verifiedBy,
            $verifiedAt,
        );
    }

    public function verify(string $verifiedBy): void
    {
        if ($this->verificationStatus !== InvestorVerificationStatus::Pending) {
            throw new DomainException("Investor profile {$this->id} has already been decided ({$this->verificationStatus->value}).");
        }

        $this->verificationStatus = InvestorVerificationStatus::Verified;
        $this->verifiedBy = $verifiedBy;
        $this->verifiedAt = now();
    }

    public function reject(string $rejectedBy): void
    {
        if ($this->verificationStatus !== InvestorVerificationStatus::Pending) {
            throw new DomainException("Investor profile {$this->id} has already been decided ({$this->verificationStatus->value}).");
        }

        $this->verificationStatus = InvestorVerificationStatus::Rejected;
        $this->verifiedBy = $rejectedBy;
        $this->verifiedAt = now();
    }

    public function isVerified(): bool
    {
        return $this->verificationStatus === InvestorVerificationStatus::Verified;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function investorUserId(): string
    {
        return $this->investorUserId;
    }

    public function investorType(): InvestorType
    {
        return $this->investorType;
    }

    public function verificationStatus(): InvestorVerificationStatus
    {
        return $this->verificationStatus;
    }

    public function companyName(): ?string
    {
        return $this->companyName;
    }

    public function investmentRange(): ?string
    {
        return $this->investmentRange;
    }

    public function preferredIndustry(): ?string
    {
        return $this->preferredIndustry;
    }

    public function riskAppetite(): ?string
    {
        return $this->riskAppetite;
    }

    public function verifiedBy(): ?string
    {
        return $this->verifiedBy;
    }

    public function verifiedAt(): ?CarbonInterface
    {
        return $this->verifiedAt;
    }
}
