<?php

namespace App\Modules\Investor\Interfaces\Http\Resources;

use App\Modules\Investor\Domain\Entities\AccessRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AccessRequest */
class AccessRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var AccessRequest $accessRequest */
        $accessRequest = $this->resource;

        return [
            'id' => $accessRequest->id(),
            'project_id' => $accessRequest->projectId(),
            'investor_user_id' => $accessRequest->investorUserId(),
            'status' => $accessRequest->status()->value,
            'requested_at' => $accessRequest->requestedAt()->toIso8601String(),
            'decided_by' => $accessRequest->decidedBy(),
            'decided_at' => $accessRequest->decidedAt()?->toIso8601String(),
        ];
    }
}
