<?php declare(strict_types=1);
namespace App\Domain\ApiUser;

use DateTime;

readonly class ApiUser
{
    public function __construct(
        public int $id,
        public string $name,
        public string $token,
        public ?string $description,
        public bool $isActive,
        public ?DateTime $validFrom,
        public ?DateTime $validTo,
        public DateTime $createdAt,
    ) {
    }
}
