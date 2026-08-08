<?php

namespace App\Modules\DataRoom\Application\Services;

use App\Modules\DataRoom\Domain\Events\DataRoomAccessRevoked;
use App\Modules\DataRoom\Domain\Repositories\DataRoomGrantRepositoryInterface;
use RuntimeException;

/** RevokeDataRoomAccessService — proposed API-013. BR-045: immediate, revoked_at-based, never a delete. */
class RevokeDataRoomAccessService
{
    public function __construct(private readonly DataRoomGrantRepositoryInterface $grants)
    {
    }

    public function execute(string $grantId, string $revokedByUserId): void
    {
        $grant = $this->grants->find($grantId);

        if (! $grant) {
            throw new RuntimeException("DataRoomGrant {$grantId} not found.");
        }

        $grant->revoke($revokedByUserId);
        $this->grants->save($grant);

        DataRoomAccessRevoked::dispatch($grant->id(), $revokedByUserId);
    }
}
