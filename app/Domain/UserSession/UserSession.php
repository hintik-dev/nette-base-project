<?php declare(strict_types=1);
namespace App\Domain\UserSession;

use DateTimeImmutable;

readonly class UserSession
{
    public function __construct(
        public int $id,
        public int $userId,
        public ?string $ipAddress,
        public ?string $userAgent,
        public string $token,
        public ?int $expireDelta,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $lastActivityAt,
        public ?DateTimeImmutable $loggedOutAt,
        public ?LogoutReason $logoutReason,
    ) {
    }


    public function isActive(): bool
    {
        return $this->logoutReason === null;
    }
}
