<?php declare(strict_types=1);
namespace App\Domain\User;

use DateTimeInterface;
use Nette\Security\Resource;

readonly class User implements Resource
{
    public const string RESOURCE_ID = 'user';

    public function __construct(
        public int $id,
        public string $email,
        public string $passwordHash,
        public bool $isSuperadmin,
        public bool $active,
        public ?DateTimeInterface $lastLogin,
    ) {
    }


    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
