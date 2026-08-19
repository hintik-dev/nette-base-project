<?php declare(strict_types=1);

namespace App\Domain\PasswordReset;

use App\Core\Database\ExplorerRepository;
use DateTimeImmutable;
use Nette\Database\Table\ActiveRow;

class ExplorerPasswordResetTokenRepository extends ExplorerRepository
{
    public const string TABLE_NAME = 'password_reset_token';

    public const string COLUMN_ID = 'id';
    public const string COLUMN_USER_ID = 'user_id';
    public const string COLUMN_TOKEN_HASH = 'token_hash';
    public const string COLUMN_EXPIRES_AT = 'expires_at';
    public const string COLUMN_USED_AT = 'used_at';
    public const string COLUMN_CREATED_AT = 'created_at';


    public function __construct(
        private readonly ExplorerPasswordResetTokenMapper $mapper,
    ) {
        parent::__construct(self::TABLE_NAME);
    }


    public function create(int $userId, string $tokenHash, DateTimeImmutable $expiresAt): int
    {
        $row = $this->getTable()->insert([
            self::COLUMN_USER_ID    => $userId,
            self::COLUMN_TOKEN_HASH => $tokenHash,
            self::COLUMN_EXPIRES_AT => $expiresAt->format('Y-m-d H:i:s'),
            self::COLUMN_CREATED_AT => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);

        assert($row instanceof ActiveRow);

        return (int) $row[self::COLUMN_ID];
    }


    public function findValidByTokenHash(string $tokenHash): ?PasswordResetToken
    {
        $row = $this->getTable()
            ->where(self::COLUMN_TOKEN_HASH, $tokenHash)
            ->where(self::COLUMN_USED_AT . ' IS NULL')
            ->where(self::COLUMN_EXPIRES_AT . ' > ?', (new DateTimeImmutable())->format('Y-m-d H:i:s'))
            ->fetch();

        return $row !== null ? $this->mapper->mapPasswordResetToken($row) : null;
    }


    public function markUsed(int $id): void
    {
        $this->getTable()
            ->where(self::COLUMN_ID, $id)
            ->update([
                self::COLUMN_USED_AT => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            ]);
    }


    /** Zruší dosud nespotřebované tokeny uživatele — nová žádost o reset ruší ty předchozí. */
    public function invalidateAllForUser(int $userId): void
    {
        $this->getTable()
            ->where(self::COLUMN_USER_ID, $userId)
            ->where(self::COLUMN_USED_AT . ' IS NULL')
            ->delete();
    }
}
