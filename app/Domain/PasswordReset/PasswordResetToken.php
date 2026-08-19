<?php declare(strict_types=1);

namespace App\Domain\PasswordReset;

use DateTimeImmutable;

readonly class PasswordResetToken
{
    public function __construct(
        public int $id,
        public int $userId,
        public string $tokenHash,
        public DateTimeImmutable $expiresAt,
        public ?DateTimeImmutable $usedAt,
        public DateTimeImmutable $createdAt,
    ) {
    }
}
