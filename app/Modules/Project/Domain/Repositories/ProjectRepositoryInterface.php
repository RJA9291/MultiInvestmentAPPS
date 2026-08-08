<?php

namespace App\Modules\Project\Domain\Repositories;

use App\Modules\Project\Domain\Entities\Project;

interface ProjectRepositoryInterface
{
    public function find(string $id): ?Project;

    public function findByCode(string $projectCode): ?Project;

    /**
     * @return array<int, Project>
     *
     * Added this Sprint for the Investor Module's "Browse Projects" flow
     * (proposed API-024, `GET /v1/projects`) — only Published projects are
     * ever visible to an Investor (BR-XXX, new this Sprint: an unapproved/
     * unpublished Project must never appear in a Browse Projects listing).
     */
    public function findPublished(): array;

    /**
     * Added this Sprint for the Dashboard & Analytics Module (proposed
     * API-029/030) — Management/Compliance dashboard counters. Kept as a
     * plain count, not a full `findByStatus(): array` hydration, since no
     * dashboard use case needs the hydrated Aggregates themselves.
     */
    public function countAll(): int;

    /** @param array<int, string> $statuses */
    public function countByStatuses(array $statuses): int;

    public function save(Project $project): void;
}
