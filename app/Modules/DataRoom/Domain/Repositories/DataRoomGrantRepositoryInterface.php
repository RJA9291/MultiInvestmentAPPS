<?php

namespace App\Modules\DataRoom\Domain\Repositories;

use App\Modules\DataRoom\Domain\Entities\DataRoomGrant;

interface DataRoomGrantRepositoryInterface
{
    public function find(string $id): ?DataRoomGrant;

    /** DB-008's Business Key: (document_id, grantee_user_id). */
    public function findForDocumentAndUser(string $documentId, string $granteeUserId): ?DataRoomGrant;

    public function save(DataRoomGrant $grant): void;
}
