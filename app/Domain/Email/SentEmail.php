<?php declare(strict_types=1);

namespace App\Domain\Email;

use DateTimeImmutable;

readonly class SentEmail
{
    public function __construct(
        public int $id,
        public string $recipient,
        public string $subject,
        public ?string $bodyHtml,
        public SentEmailStatus $status,
        public ?string $error,
        public ?DateTimeImmutable $sentAt,
        public DateTimeImmutable $createdAt,
    ) {
    }
}
