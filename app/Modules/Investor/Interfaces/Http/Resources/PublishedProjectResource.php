<?php

namespace App\Modules\Investor\Interfaces\Http\Resources;

use App\Modules\Project\Domain\Entities\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * PublishedProjectResource — the Investor-facing "Browse Projects" shape
 * (proposed API-024). Deliberately a SEPARATE Resource from any internal
 * Project representation — never exposes `owner_user_id` or any field an
 * outside Investor should not see; only what a Browse Projects list needs.
 *
 * @mixin Project
 */
class PublishedProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Project $project */
        $project = $this->resource;

        return [
            'id' => $project->id(),
            'project_code' => $project->projectCode(),
            'title' => $project->title(),
            'description' => $project->description(),
            'category' => $project->category(),
        ];
    }
}
