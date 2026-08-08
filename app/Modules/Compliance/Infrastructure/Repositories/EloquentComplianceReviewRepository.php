<?php

namespace App\Modules\Compliance\Infrastructure\Repositories;

use App\Modules\Compliance\Domain\Entities\ComplianceReview;
use App\Modules\Compliance\Domain\Repositories\ComplianceReviewRepositoryInterface;
use App\Modules\Compliance\Infrastructure\Eloquent\ComplianceReviewCommentModel;
use App\Modules\Compliance\Infrastructure\Eloquent\ComplianceReviewModel;
use App\Modules\Compliance\Infrastructure\Mappers\ComplianceReviewMapper;

class EloquentComplianceReviewRepository implements ComplianceReviewRepositoryInterface
{
    public function __construct(private readonly ComplianceReviewMapper $mapper)
    {
    }

    public function find(string $id): ?ComplianceReview
    {
        $model = ComplianceReviewModel::find($id);

        return $model ? $this->mapper->toDomain($model) : null;
    }

    /** The current open (Pending) cycle for a Project, if one exists. */
    public function findActiveCycleForProject(string $projectId): ?ComplianceReview
    {
        $model = ComplianceReviewModel::where('project_id', $projectId)
            ->where('status', 'pending')
            ->latest('cycle_number')
            ->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    /**
     * Persists the ComplianceReview row AND any newly added, not-yet-persisted
     * comments (ComplianceReview::pendingComments()) in one unit of work.
     * WAJIB: comment rows are append-only — never updated once inserted.
     */
    public function save(ComplianceReview $review): void
    {
        $existing = ComplianceReviewModel::find($review->id());
        $model = $this->mapper->toModel($review, $existing);
        $model->save();

        $alreadyPersisted = ComplianceReviewCommentModel::where('compliance_review_id', $review->id())->count();
        $pending = $review->pendingComments();

        foreach (array_slice($pending, $alreadyPersisted) as $comment) {
            ComplianceReviewCommentModel::create([
                'compliance_review_id' => $review->id(),
                'comment' => $comment,
            ]);
        }
    }

    public function countByStatus(string $status): int
    {
        return ComplianceReviewModel::where('status', $status)->count();
    }
}
