<?php declare(strict_types=1);
namespace App\Domain\User;

use App\Core\Database\ExplorerRepository;
use DateTimeInterface;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use RuntimeException;

class ExplorerUserRepository extends ExplorerRepository
{
    public const string COLUMN_ID = 'id';
    public const string COLUMN_EMAIL = 'email';
    public const string COLUMN_PASSWORD_HASH = 'password_hash';
    public const string COLUMN_IS_SUPERADMIN = 'is_superadmin';

    public const string COLUMN_ACTIVE = 'active';
    public const string COLUMN_LAST_LOGIN = 'last_login';

    public const string TABLE_NAME = 'user';


    public function __construct(
        private readonly ExplorerUserMapper $userMapper,
    ) {
        parent::__construct(self::TABLE_NAME);
    }


    /** @return Selection<ActiveRow> */
    public function getAllDataSource(): Selection
    {
        return $this->findAll();
    }


    /** @return User[] */
    public function getActiveUsers(): array
    {
        return array_values(array_map(
            fn(ActiveRow $row): User => $this->userMapper->mapUser($row),
            iterator_to_array(
                $this->getTable()
                    ->where(self::COLUMN_ACTIVE, true)
                    ->order(self::COLUMN_EMAIL . ' ASC'),
            ),
        ));
    }


    /**
     * @throws UserNotFoundException
     */
    public function getUserById(int $id): User
    {
        $userRow = $this->getTable()
            ->where(self::COLUMN_ID, $id)
            ->fetch();

        if ($userRow === null) {
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

        if ($userRow === null) {
            throw new UserNotFoundException();
        }

        return $this->userMapper->mapUser($userRow);
    }


    public function createUser(string $email, string $passwordHash, bool $active): User
    {
        $row = $this->getTable()->insert([
            self::COLUMN_EMAIL => $email,
            self::COLUMN_PASSWORD_HASH => $passwordHash,
            self::COLUMN_ACTIVE => $active,
        ]);

        if (!($row instanceof ActiveRow))
        {
            throw new RuntimeException(
                'Failed to create user',
            );
        }

        return $this->userMapper->mapUser($row);
    }


    /** @return int[] */
    public function getActiveUserIds(): array
    {
        return array_values(array_map(
            static fn(ActiveRow $row): int => (int) $row[self::COLUMN_ID],
            iterator_to_array($this->getTable()->where(self::COLUMN_ACTIVE, true)),
        ));
    }


    public function userExistsByEmail(string $email, ?int $excludeId = null): bool
    {
        $selection = $this->getTable()
            ->where(self::COLUMN_EMAIL, $email);

        if ($excludeId !== null) {
            $selection->where(self::COLUMN_ID . ' != ?', $excludeId);
        }

        return $selection->count() > 0;
    }


    public function updateUserPasswordHash(int $id, string $passwordHash): void
    {
        $this->getTable()
            ->where(self::COLUMN_ID, $id)
            ->update([
                self::COLUMN_PASSWORD_HASH => $passwordHash,
            ]);
    }


    public function updateUser(int $id, string $email, bool $active): void
    {
        $this->getTable()
            ->where(self::COLUMN_ID, $id)
            ->update([
                self::COLUMN_EMAIL => $email,
                self::COLUMN_ACTIVE => $active,
            ]);
    }


    /**
     * Bypass ACL se nastavuje jen z CLI (app:create-superadmin), ne z administrace —
     * nikdo si tak nemůže sám udělit obejití oprávnění.
     */
    public function setSuperadmin(int $id, bool $isSuperadmin): void
    {
        $this->getTable()
            ->where(self::COLUMN_ID, $id)
            ->update([
                self::COLUMN_IS_SUPERADMIN => $isSuperadmin,
            ]);
    }


    public function setActive(int $id, bool $active): void
    {
        $this->getTable()
            ->where(self::COLUMN_ID, $id)
            ->update([
                self::COLUMN_ACTIVE => $active,
            ]);
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
