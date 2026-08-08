<?php

namespace App\Modules\DataRoom\Domain\Entities;

use Carbon\CarbonInterface;

/**
 * NdaAcknowledgment Entity (06_DOMAIN_MODEL.md §3.3, BR-042, BR-133)
 *
 * Proof-of-acceptance record — never edited once created. BR-133: captures
 * the exact acknowledged NDA version as a content hash, never a boolean.
 */
class NdaAcknowledgment
{
    private function __construct(
        private readonly string $id,
        private readonly string $dataRoomGrantId,
        private readonly string $ndaVersionHash,
        private readonly CarbonInterface $acknowledgedAt,
        private readonly ?string $acknowledgedIp,
    ) {
    }

    public static function acknowledge(
        string $id,
        string $dataRoomGrantId,
        string $ndaVersionHash,
        ?string $acknowledgedIp,
    ): self {
        return new self($id, $dataRoomGrantId, $ndaVersionHash, now(), $acknowledgedIp);
    }

    public static function reconstitute(
        string $id,
        string $dataRoomGrantId,
        string $ndaVersionHash,
        CarbonInterface $acknowledgedAt,
        ?string $acknowledgedIp,
    ): self {
        return new self($id, $dataRoomGrantId, $ndaVersionHash, $acknowledgedAt, $acknowledgedIp);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function dataRoomGrantId(): string
    {
        return $this->dataRoomGrantId;
    }

    public function ndaVersionHash(): string
    {
        return $this->ndaVersionHash;
    }

    public function acknowledgedAt(): CarbonInterface
    {
        return $this->acknowledgedAt;
    }

    public function acknowledgedIp(): ?string
    {
        return $this->acknowledgedIp;
    }
}
