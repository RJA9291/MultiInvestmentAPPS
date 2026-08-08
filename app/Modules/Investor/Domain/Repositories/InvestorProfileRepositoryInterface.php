<?php

namespace App\Modules\Investor\Domain\Repositories;

use App\Modules\Investor\Domain\Entities\InvestorProfile;

interface InvestorProfileRepositoryInterface
{
    public function find(string $id): ?InvestorProfile;

    public function findByInvestorUserId(string $investorUserId): ?InvestorProfile;

    public function save(InvestorProfile $profile): void;

    /** Added this Sprint for the Dashboard & Analytics Module's Management Dashboard "Active Investors" KPI (proposed API-029). */
    public function countByVerificationStatus(string $status): int;

    /** Added this Sprint for the Dashboard & Analytics Module's "Total Investors" KPI (proposed API-029). */
    public function countAll(): int;

    /**
     * @return array<int, InvestorProfile>
     *
     * Added this Sprint for the AI Investor-Project Matching Engine
     * (proposed API-033) — candidate pool for "match this newly-Published
     * Project against every eligible Investor." Only `VERIFIED` profiles
     * are ever eligible, mirroring BR-143's existing access-request
     * eligibility rule exactly.
     */
    public function findAllVerified(): array;
}
