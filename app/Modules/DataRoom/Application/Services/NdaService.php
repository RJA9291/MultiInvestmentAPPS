<?php

namespace App\Modules\DataRoom\Application\Services;

use App\Modules\DataRoom\Domain\Entities\NdaAcknowledgment;
use App\Modules\DataRoom\Domain\Events\NdaAcknowledged;
use App\Modules\DataRoom\Domain\Repositories\NdaAcknowledgmentRepositoryInterface;
use Illuminate\Support\Str;

/**
 * NdaService — proposed API-014 (POST /v1/data-room-grants/{id}/nda-acknowledgment).
 * Entirely absent from the Project Owner's Data-Room brief; built here
 * because it is already a locked, mandatory part of the DataRoomGrant
 * Aggregate (BR-042, BR-133) — see DomainEntities/NdaAcknowledgment.php.
 */
class NdaService
{
    public function __construct(private readonly NdaAcknowledgmentRepositoryInterface $acknowledgments)
    {
    }

    public function acknowledge(string $dataRoomGrantId, string $ndaVersionHash, ?string $acknowledgedIp): NdaAcknowledgment
    {
        $acknowledgment = NdaAcknowledgment::acknowledge(
            id: (string) Str::uuid(),
            dataRoomGrantId: $dataRoomGrantId,
            ndaVersionHash: $ndaVersionHash,
            acknowledgedIp: $acknowledgedIp,
        );

        $this->acknowledgments->save($acknowledgment);

        NdaAcknowledged::dispatch($acknowledgment->id(), $ndaVersionHash, (string) $acknowledgment->acknowledgedAt());

        return $acknowledgment;
    }
}
