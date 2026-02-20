<?php

namespace App\Domain\User;

use App\Core\Database\ExplorerRepository;
use DateTimeInterface;

class ExplorerUserRepository extends ExplorerRepository
{
    public const string COLUMN_ID = 'id';
    public const string COLUMN_EMAIL = 'email';
    public const string COLUMN_PASSWORD_HASH = 'password_hash';
    public const string COLUMN_ROLE = 'role';

    public const string COLUMN_ACTIVE = 'active';
    public const string COLUMN_LAST_LOGIN = 'last_login';

    public const string TABLE_NAME = 'user';


    public function __construct(
        private readonly ExplorerUserMapper $userMapper,
    )
    {
        parent::__construct(self::TABLE_NAME);
    }


    /**
     * @throws UserNotFoundException
     */
    public function getUserById(int $id): User
    {
        $userRow = $this->getTable()
            ->where(self::COLUMN_ID, $id)
            ->fetch();

        if ($userRow === null)
        {
            throw new UserNotFoundException();
        }

        return $this->userMapper->mapUser($userRow);
    }


    /**
     * @throws UserNotFoundException
     */
    public function getUserByEmail(string $email): User
    {
        $userRow = $this->getTable()
            ->where(self::COLUMN_EMAIL, $email)
            ->fetch();

        if ($userRow === null)
        {
            throw new UserNotFoundException();
        }

        return $this->userMapper->mapUser($userRow);
    }


    public function updateUserLastLogin(int $id, DateTimeInterface $lastLogin): void
    {
        $this->getTable()
            ->where(self::COLUMN_ID, $id)
            ->update([
                self::COLUMN_LAST_LOGIN => $lastLogin,
            ]);
    }
}
