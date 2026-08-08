<?php

namespace App\Modules\DataRoom\Infrastructure\Repositories;

use App\Modules\DataRoom\Domain\Entities\NdaAcknowledgment;
use App\Modules\DataRoom\Domain\Repositories\NdaAcknowledgmentRepositoryInterface;
use App\Modules\DataRoom\Infrastructure\Eloquent\NdaAcknowledgmentModel;
use App\Modules\DataRoom\Infrastructure\Mappers\NdaAcknowledgmentMapper;

class EloquentNdaAcknowledgmentRepository implements NdaAcknowledgmentRepositoryInterface
{
    public function __construct(private readonly NdaAcknowledgmentMapper $mapper)
    {
    }

    public function hasAcknowledgment(string $dataRoomGrantId): bool
    {
        return NdaAcknowledgmentModel::where('data_room_grant_id', $dataRoomGrantId)->exists();
    }

    public function save(NdaAcknowledgment $acknowledgment): void
    {
        $this->mapper->toModel($acknowledgment)->save();
    }
}
