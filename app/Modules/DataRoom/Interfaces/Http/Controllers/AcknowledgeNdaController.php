<?php

namespace App\Modules\DataRoom\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\DataRoom\Application\Services\NdaService;
use App\Modules\DataRoom\Interfaces\Http\Requests\AcknowledgeNdaRequest;
use Illuminate\Http\Request;

/** Proposed API-014: POST /v1/data-room-grants/{id}/nda-acknowledgment */
class AcknowledgeNdaController
{
    public function __invoke(string $id, AcknowledgeNdaRequest $request, NdaService $service, Request $rawRequest)
    {
        $ack = $service->acknowledge(
            dataRoomGrantId: $id,
            ndaVersionHash: $request->validated()['nda_version_hash'],
            acknowledgedIp: $rawRequest->ip(),
        );

        return ApiResponse::success([
            'id' => $ack->id(),
            'data_room_grant_id' => $ack->dataRoomGrantId(),
            'nda_version_hash' => $ack->ndaVersionHash(),
            'acknowledged_at' => $ack->acknowledgedAt()->toIso8601String(),
        ], status: 201);
    }
}
