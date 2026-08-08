<?php

namespace App\Modules\Project\Application\Services;

use App\Modules\Project\Domain\Entities\Project;
use App\Modules\Project\Domain\Events\ProjectApproved;
use App\Modules\Project\Domain\Events\ProjectArchived;
use App\Modules\Project\Domain\Events\ProjectCreated;
use App\Modules\Project\Domain\Events\ProjectPublished;
use App\Modules\Project\Domain\Events\ProjectResubmitted;
use App\Modules\Project\Domain\Events\ProjectSubmitted;
use App\Modules\Project\Domain\Events\ProjectUnpublished;
use App\Modules\Project\Domain\Policies\ComplianceReviewGatePolicy;
use App\Modules\Project\Domain\Policies\PublishEligibilityPolicy;
use App\Modules\Project\Domain\Repositories\ProjectRepositoryInterface;
use DomainException;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * ProjectPublishingService (06_DOMAIN_MODEL.md §3.1's Application Service).
 * Holds the business logic and orchestration for the Project lifecycle —
 * WAJIB: Controllers never contain this logic (14_LARAVEL_BLUEPRINT.md §4/§21).
 */
class ProjectPublishingService
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly PublishEligibilityPolicy $publishEligibilityPolicy,
        private readonly ComplianceReviewGatePolicy $complianceReviewGatePolicy,
    ) {
    }

    public function create(string $ownerUserId, string $title, ?string $description, ?string $category): Project
    {
        $project = Project::createDraft(
            id: (string) Str::uuid(),
            projectCode: $this->generateProjectCode(),
            ownerUserId: $ownerUserId,
            title: $title,
            description: $description,
            category: $category,
        );

        $this->projects->save($project);

        ProjectCreated::dispatch($project->id(), ['project_code' => $project->projectCode()]);

        return $project;
    }

    /** Draft -> Submitted. Opens a new Compliance review cycle (via listener on ProjectSubmitted). */
    public function submit(string $projectId): Project
    {
        $project = $this->requireProject($projectId);

        $project->submit();
        $this->projects->save($project);

        ProjectSubmitted::dispatch($project->id());

        return $project;
    }

    /** Business Owner's own action after correcting a ReturnedToBusinessOwner project. */
    public function resubmit(string $projectId): Project
    {
        $project = $this->requireProject($projectId);

        $project->resubmit();
        $this->projects->save($project);

        ProjectResubmitted::dispatch($project->id());

        return $project;
    }

    /**
     * Reactive only — called by Application/Listeners/OnProjectComplianceApproved,
     * never directly by a Controller (this is the Anti-Corruption Layer
     * translation from Compliance Context's ProjectComplianceApproved,
     * 06_DOMAIN_MODEL.md §3.1).
     */
    public function markApprovedFromCompliance(string $projectId): void
    {
        $project = $this->requireProject($projectId);

        $project->markApproved();
        $this->projects->save($project);

        ProjectApproved::dispatch($project->id());
    }

    public function markReturnedFromCompliance(string $projectId): void
    {
        $project = $this->requireProject($projectId);

        $project->markReturnedToBusinessOwner();
        $this->projects->save($project);
    }

    /**
     * WAJIB: PublishEligibilityPolicy (BR-017) and ComplianceReviewGatePolicy
     * (BR-139/BR-140) are checked here, at the Service layer, BEFORE the
     * Entity's own transitionTo() state-machine check runs — the Policy
     * checks are business eligibility rules, the Entity check is structural
     * state-machine validity; both must pass.
     */
    public function publish(string $projectId, int $approvedDocumentCount): Project
    {
        $project = $this->requireProject($projectId);

        if (! $this->complianceReviewGatePolicy->canPublish($project->status())) {
            throw new DomainException('Project is not in an Approved compliance state and cannot be published.');
        }

        if (! $this->publishEligibilityPolicy->isEligible($project, $approvedDocumentCount)) {
            throw new DomainException('Project does not meet publish eligibility requirements (BR-017).');
        }

        $project->publish();
        $this->projects->save($project);

        ProjectPublished::dispatch($project->id());

        return $project;
    }

    public function unpublish(string $projectId): Project
    {
        $project = $this->requireProject($projectId);

        $project->unpublish();
        $this->projects->save($project);

        ProjectUnpublished::dispatch($project->id());

        return $project;
    }

    public function archive(string $projectId): Project
    {
        $project = $this->requireProject($projectId);

        $project->archive();
        $this->projects->save($project);

        ProjectArchived::dispatch($project->id());

        return $project;
    }

    private function requireProject(string $projectId): Project
    {
        $project = $this->projects->find($projectId);

        if (! $project) {
            throw new RuntimeException("Project {$projectId} not found.");
        }

        return $project;
    }

    /**
     * project_code generation is an implementation detail not specified by
     * 06_DOMAIN_MODEL.md/08_DATABASE_DESIGN.md beyond "unique, human-readable"
     * — flagged here as a pragmatic choice (sequential-looking but not
     * guessable), not a documented Business Rule.
     */
    private function generateProjectCode(): string
    {
        return 'PRJ-' . strtoupper(Str::random(8));
    }
}
