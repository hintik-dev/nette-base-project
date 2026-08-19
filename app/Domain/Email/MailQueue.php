<?php declare(strict_types=1);

namespace App\Domain\Email;

use DateTimeImmutable;

readonly class MailQueue
{
    public function __construct(
        public int $id,
        public string $recipient,
        public string $subject,
        public string $bodyHtml,
        public ?string $mailClass,
        public bool $isSensitive,
        public int $priority,
        public MailQueueStatus $status,
        public int $attempts,
        public int $maxAttempts,
        public ?DateTimeImmutable $nextAttemptAt,
        public ?DateTimeImmutable $lockedAt,
        public ?string $lockedBy,
        public ?string $error,
        public DateTimeImmutable $createdAt,
    ) {
    }
}
