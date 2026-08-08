<?php

namespace App\Modules\Investor\Domain\Repositories;

use App\Modules\Investor\Domain\Entities\AccessRequest;

interface AccessRequestRepositoryInterface
{
    public function find(string $id): ?AccessRequest;

    /** @return array<int, AccessRequest> */
    public function findByProjectId(string $projectId): array;

    public function save(AccessRequest $request): void;

    /** Added this Sprint for the Dashboard & Analytics Module's Investor KPIs (proposed API-029), platform-wide (not per-project). */
    public function countAll(): int;

    /** @see \App\Modules\Investor\Domain\ValueObjects\AccessRequestStatus */
    public function countByStatus(string $status): int;
}
