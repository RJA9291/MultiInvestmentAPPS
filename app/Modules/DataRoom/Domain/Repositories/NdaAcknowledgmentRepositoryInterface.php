<?php

namespace App\Modules\DataRoom\Domain\Repositories;

use App\Modules\DataRoom\Domain\Entities\NdaAcknowledgment;

interface NdaAcknowledgmentRepositoryInterface
{
    /** Whether the grant has ANY acknowledgment on file — BR-042 default (project-wide, per-grant here). */
    public function hasAcknowledgment(string $dataRoomGrantId): bool;

    public function save(NdaAcknowledgment $acknowledgment): void;
}
