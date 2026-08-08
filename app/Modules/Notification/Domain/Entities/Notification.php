<?php

namespace App\Modules\Notification\Domain\Entities;

use App\Modules\Notification\Domain\ValueObjects\NotificationType;
use Carbon\CarbonInterface;
use DomainException;

/**
 * Notification Aggregate Root (06_DOMAIN_MODEL.md §8, DB-028).
 *
 * `is_read` boolean + `queued_at`/`delivered_at`/`read_at` — NOT the
 * Project Owner's proposed `status` string (`UNREAD`/`READ`) — this is the
 * already-locked shape, predating this Sprint's brief.
 */
class Notification
{
    private function __construct(
        private readonly string $id,
        private readonly string $recipientUserId,
        private readonly NotificationType $notificationType,
        private readonly string $title,
        private readonly string $message,
        private readonly array $metadata,
        private bool $isRead,
        private readonly CarbonInterface $queuedAt,
        private ?CarbonInterface $deliveredAt,
        private ?CarbonInterface $readAt,
    ) {
    }

    public static function queue(
        string $id,
        string $recipientUserId,
        NotificationType $notificationType,
        string $title,
        string $message,
        array $metadata = [],
    ): self {
        return new self($id, $recipientUserId, $notificationType, $title, $message, $metadata, false, now(), null, null);
    }

    public static function reconstitute(
        string $id,
        string $recipientUserId,
        NotificationType $notificationType,
        string $title,
        string $message,
        array $metadata,
        bool $isRead,
        CarbonInterface $queuedAt,
        ?CarbonInterface $deliveredAt,
        ?CarbonInterface $readAt,
    ): self {
        return new self($id, $recipientUserId, $notificationType, $title, $message, $metadata, $isRead, $queuedAt, $deliveredAt, $readAt);
    }

    /** EVT-053 NotificationDelivered — in-app "delivery" is simply the row existing, so this is called synchronously right after queue(). */
    public function markDelivered(): void
    {
        if ($this->deliveredAt !== null) {
            return; // idempotent — already delivered (e.g. in-app + email both call this)
        }

        $this->deliveredAt = now();
    }

    /** EVT-054 NotificationRead. */
    public function markAsRead(): void
    {
        if ($this->isRead) {
            throw new DomainException("Notification {$this->id} is already read.");
        }

        $this->isRead = true;
        $this->readAt = now();
    }

    public function id(): string
    {
        return $this->id;
    }

    public function recipientUserId(): string
    {
        return $this->recipientUserId;
    }

    public function notificationType(): NotificationType
    {
        return $this->notificationType;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function queuedAt(): CarbonInterface
    {
        return $this->queuedAt;
    }

    public function deliveredAt(): ?CarbonInterface
    {
        return $this->deliveredAt;
    }

    public function readAt(): ?CarbonInterface
    {
        return $this->readAt;
    }
}
