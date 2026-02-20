<?php

namespace App\Domain\User;

use App\Domain\UserRole\UserRole;
use DateTime;
use Nette\Security\Resource;

readonly class User implements Resource
{
    public const string RESOURCE_ID = 'user';

    public function __construct(
        public int $id,
        public string $email,
        public string $passwordHash,
        public UserRole $role,
        public bool $active,
        public ?DateTime $lastLogin,
    )
    {

    }


    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
