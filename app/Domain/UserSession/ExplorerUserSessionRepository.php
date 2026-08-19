<?php declare(strict_types=1);
namespace App\Domain\UserSession;

use App\Core\Database\ExplorerRepository;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class ExplorerUserSessionRepository extends ExplorerRepository
{
    public const string COLUMN_ID = 'id';
    public const string COLUMN_USER_ID = 'user_id';
    public const string COLUMN_IP_ADDRESS = 'ip_address';
    public const string COLUMN_USER_AGENT = 'user_agent';
    public const string COLUMN_TOKEN = 'token';
    public const string COLUMN_EXPIRE_DELTA = 'expire_delta';
    public const string COLUMN_CREATED_AT = 'created_at';
    public const string COLUMN_LAST_ACTIVITY_AT = 'last_activity_at';
    public const string COLUMN_LOGGED_OUT_AT = 'logged_out_at';
    public const string COLUMN_LOGOUT_REASON = 'logout_reason';

    public const string TABLE_NAME = 'user_session';


    public function __construct(
        private readonly ExplorerUserSessionMapper $mapper,
    ) {
        parent::__construct(self::TABLE_NAME);
    }


    public function create(int $userId, ?string $ipAddress, ?string $userAgent, string $token, ?int $expireDelta): UserSession
    {
        $row = $this->getTable()->insert([
            self::COLUMN_USER_ID          => $userId,
            self::COLUMN_IP_ADDRESS       => $ipAddress,
            self::COLUMN_USER_AGENT       => $userAgent,
            self::COLUMN_TOKEN            => $token,
            self::COLUMN_EXPIRE_DELTA     => $expireDelta,
            self::COLUMN_CREATED_AT       => date('Y-m-d H:i:s'),
            self::COLUMN_LAST_ACTIVITY_AT => date('Y-m-d H:i:s'),
        ]);
        assert($row instanceof ActiveRow);

        return $this->mapper->mapUserSession($row);
    }


    public function findByToken(string $token): ?UserSession
    {
        $row = $this->getTable()->where(self::COLUMN_TOKEN, $token)->fetch();

        if ($row === null) {
            return null;
        }

        return $this->mapper->mapUserSession($row);
    }


    public function updateExpireDelta(int $id, ?int $expireDelta): void
    {
        $this->getTable()
            ->where(self::COLUMN_ID, $id)
            ->update([self::COLUMN_EXPIRE_DELTA => $expireDelta]);
    }


    public function findById(int $id): ?UserSession
    {
        $row = $this->find($id);

        if ($row === null) {
            return null;
        }

        return $this->mapper->mapUserSession($row);
    }


    public function updateLastActivity(int $id): void
    {
        $this->getTable()
            ->where(self::COLUMN_ID, $id)
            ->update([self::COLUMN_LAST_ACTIVITY_AT => date('Y-m-d H:i:s')]);
    }


    public function markAsLoggedOut(int $id, LogoutReason $reason): void
    {
        $this->getTable()
            ->where(self::COLUMN_ID, $id)
            ->update([
                self::COLUMN_LOGGED_OUT_AT  => date('Y-m-d H:i:s'),
                self::COLUMN_LOGOUT_REASON  => $reason->value,
            ]);
    }


    /** Odhlásí všechny aktivní session daného uživatele (např. po resetu hesla). */
    public function markAllActiveAsLoggedOutForUser(int $userId, LogoutReason $reason): void
    {
        $this->getTable()
            ->where(self::COLUMN_USER_ID, $userId)
            ->where(self::COLUMN_LOGGED_OUT_AT . ' IS NULL')
            ->update([
                self::COLUMN_LOGGED_OUT_AT => date('Y-m-d H:i:s'),
                self::COLUMN_LOGOUT_REASON => $reason->value,
            ]);
    }


    /** @return Selection<ActiveRow> */
    public function getAllSelection(): Selection
    {
        return $this->findAll()->order(self::COLUMN_ID . ' DESC');
    }
}
