<?php

namespace App\Modules\DataRoom\Application\Services;

use App\Modules\DataRoom\Domain\Entities\DataRoomGrant;
use App\Modules\DataRoom\Domain\Events\DataRoomPermissionGranted;
use App\Modules\DataRoom\Domain\Policies\DefaultViewOnlyPolicy;
use App\Modules\DataRoom\Domain\Repositories\DataRoomGrantRepositoryInterface;
use App\Modules\DataRoom\Domain\ValueObjects\PermissionTier;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Support\Str;

/**
 * GrantDataRoomAccessService — API-005 (POST /v1/data-room-grants).
 * Replaces the Project Owner's raw DB::table()->insert() sketch with the
 * Repository pattern already established for every other Module this Sprint.
 */
class GrantDataRoomAccessService
{
    public function __construct(
        private readonly DataRoomGrantRepositoryInterface $grants,
        private readonly DefaultViewOnlyPolicy $defaultViewOnlyPolicy,
    ) {
    }

    public function execute(
        string $documentId,
        string $granteeUserId,
        string $grantedByUserId,
        string $grantedByUserRole,
        PermissionTier $permissionTier = PermissionTier::ViewOnly,
        ?CarbonInterface $expiresAt = null,
    ): DataRoomGrant {
        if (! $this->defaultViewOnlyPolicy->isAuthorizedToGrant($permissionTier, $grantedByUserRole)) {
            throw new DomainException('Only the Business Owner may grant Downloadable access (BR-041).');
        }

        $grant = DataRoomGrant::grant(
            id: (string) Str::uuid(),
            documentId: $documentId,
            granteeUserId: $granteeUserId,
            grantedBy: $grantedByUserId,
            permissionTier: $permissionTier,
            expiresAt: $expiresAt,
        );

        $this->grants->save($grant);

        DataRoomPermissionGranted::dispatch($grant->id(), $documentId, $granteeUserId, $permissionTier);

        return $grant;
    }
}
