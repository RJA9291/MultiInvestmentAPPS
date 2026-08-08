<?php

namespace App\Modules\Notification\Domain\Entities;

use App\Modules\Notification\Domain\ValueObjects\DeliveryChannel;
use App\Modules\Notification\Domain\ValueObjects\NotificationType;

/** NotificationPreference Entity (06_DOMAIN_MODEL.md §8, DB-029) — not present in the Project Owner's sketch at all; built as originally locked, not omitted. */
class NotificationPreference
{
    private function __construct(
        private readonly string $id,
        private readonly string $userId,
        private readonly NotificationType $notificationType,
        private readonly DeliveryChannel $channel,
        private bool $isEnabled,
    ) {
    }

    public static function create(
        string $id,
        string $userId,
        NotificationType $notificationType,
        DeliveryChannel $channel,
        bool $isEnabled = true,
    ): self {
        return new self($id, $userId, $notificationType, $channel, $isEnabled);
    }

    public static function reconstitute(
        string $id,
        string $userId,
        NotificationType $notificationType,
        DeliveryChannel $channel,
        bool $isEnabled,
    ): self {
        return new self($id, $userId, $notificationType, $channel, $isEnabled);
    }

    public function setEnabled(bool $isEnabled): void
    {
        $this->isEnabled = $isEnabled;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function notificationType(): NotificationType
    {
        return $this->notificationType;
    }

    public function channel(): DeliveryChannel
    {
        return $this->channel;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }
}
