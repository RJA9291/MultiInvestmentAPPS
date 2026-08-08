<?php

namespace App\Modules\Compliance\Domain\Repositories;

use App\Modules\Compliance\Domain\Entities\ComplianceReview;

interface ComplianceReviewRepositoryInterface
{
    public function find(string $id): ?ComplianceReview;

    public function findActiveCycleForProject(string $projectId): ?ComplianceReview;

    public function save(ComplianceReview $review): void;

    /** Added this Sprint for the Dashboard & Analytics Module's Compliance Dashboard "Pending Approvals"/"Pending Reviews" KPI (proposed API-030). */
    public function countByStatus(string $status): int;
}
