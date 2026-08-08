<?php

namespace App\Modules\DataRoom\Infrastructure\Mappers;

use App\Modules\DataRoom\Domain\Entities\NdaAcknowledgment;
use App\Modules\DataRoom\Infrastructure\Eloquent\NdaAcknowledgmentModel;

class NdaAcknowledgmentMapper
{
    public function toDomain(NdaAcknowledgmentModel $model): NdaAcknowledgment
    {
        return NdaAcknowledgment::reconstitute(
            id: $model->id,
            dataRoomGrantId: $model->data_room_grant_id,
            ndaVersionHash: $model->nda_version_hash,
            acknowledgedAt: $model->acknowledged_at,
            acknowledgedIp: $model->acknowledged_ip,
        );
    }

    public function toModel(NdaAcknowledgment $ack): NdaAcknowledgmentModel
    {
        $model = new NdaAcknowledgmentModel(); // never updated once created — no $existing param needed
        $model->id = $ack->id();
        $model->data_room_grant_id = $ack->dataRoomGrantId();
        $model->nda_version_hash = $ack->ndaVersionHash();
        $model->acknowledged_at = $ack->acknowledgedAt();
        $model->acknowledged_ip = $ack->acknowledgedIp();

        return $model;
    }
}
