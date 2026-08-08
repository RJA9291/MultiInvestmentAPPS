<?php

namespace App\Modules\DataRoom\Infrastructure\Repositories;

use App\Modules\DataRoom\Domain\Entities\DataRoomGrant;
use App\Modules\DataRoom\Domain\Repositories\DataRoomGrantRepositoryInterface;
use App\Modules\DataRoom\Infrastructure\Eloquent\DataRoomGrantModel;
use App\Modules\DataRoom\Infrastructure\Mappers\DataRoomGrantMapper;

class EloquentDataRoomGrantRepository implements DataRoomGrantRepositoryInterface
{
    public function __construct(private readonly DataRoomGrantMapper $mapper)
    {
    }

    public function find(string $id): ?DataRoomGrant
    {
        $model = DataRoomGrantModel::find($id);

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findForDocumentAndUser(string $documentId, string $granteeUserId): ?DataRoomGrant
    {
        $model = DataRoomGrantModel::where('document_id', $documentId)
            ->where('grantee_user_id', $granteeUserId)
            ->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function save(DataRoomGrant $grant): void
    {
        $existing = DataRoomGrantModel::find($grant->id());
        $this->mapper->toModel($grant, $existing)->save();
    }
}
