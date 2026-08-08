<?php

namespace App\Modules\Investor\Infrastructure\Repositories;

use App\Modules\Investor\Domain\Entities\InvestorProfile;
use App\Modules\Investor\Domain\Repositories\InvestorProfileRepositoryInterface;
use App\Modules\Investor\Infrastructure\Eloquent\InvestorProfileModel;
use App\Modules\Investor\Infrastructure\Mappers\InvestorProfileMapper;

class EloquentInvestorProfileRepository implements InvestorProfileRepositoryInterface
{
    public function __construct(private readonly InvestorProfileMapper $mapper)
    {
    }

    public function find(string $id): ?InvestorProfile
    {
        $model = InvestorProfileModel::find($id);

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findByInvestorUserId(string $investorUserId): ?InvestorProfile
    {
        $model = InvestorProfileModel::where('investor_user_id', $investorUserId)->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function save(InvestorProfile $profile): void
    {
        $existing = InvestorProfileModel::find($profile->id());
        $this->mapper->toModel($profile, $existing)->save();
    }

    public function countByVerificationStatus(string $status): int
    {
        return InvestorProfileModel::where('verification_status', $status)->count();
    }

    public function countAll(): int
    {
        return InvestorProfileModel::count();
    }

    public function findAllVerified(): array
    {
        return InvestorProfileModel::where('verification_status', 'VERIFIED')
            ->get()
            ->map(fn (InvestorProfileModel $model) => $this->mapper->toDomain($model))
            ->all();
    }
}
