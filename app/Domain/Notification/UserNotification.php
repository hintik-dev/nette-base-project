<?php declare(strict_types=1);

namespace App\Domain\Notification;

use DateTimeImmutable;

/**
 * Joinovaná projekce notification + notification_recipient — to, co se
 * skutečně renderuje v dropdownu a gridu (obsah + stav pro konkrétního uživatele).
 */
readonly class UserNotification
{
    public function __construct(
        public int $recipientId,
        public int $notificationId,
        public NotificationType $type,
        public string $title,
        public string $message,
        public ?string $link,
        public DateTimeImmutable $notificationCreatedAt,
        public ?DateTimeImmutable $readAt,
        public ?DateTimeImmutable $hiddenAt,
    ) {
    }


    public function isRead(): bool
    {
        return $this->readAt !== null;
    }
}
