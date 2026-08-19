<?php declare(strict_types=1);

namespace App\Domain\Notification;

use App\Core\Database\ExplorerRepository;
use DateTimeImmutable;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class ExplorerNotificationRecipientRepository extends ExplorerRepository
{
    public const string TABLE_NAME = 'notification_recipient';

    public const string COLUMN_ID = 'id';
    public const string COLUMN_NOTIFICATION_ID = 'notification_id';
    public const string COLUMN_USER_ID = 'user_id';
    public const string COLUMN_READ_AT = 'read_at';
    public const string COLUMN_HIDDEN_AT = 'hidden_at';
    public const string COLUMN_CREATED_AT = 'created_at';

    /** Sloupce joinovaného obsahu notifikace (přes reálnou FK notification_id — Nette Explorer join podle konvence). */
    public const string COLUMN_TYPE = 'type';
    public const string COLUMN_TITLE = 'title';
    public const string COLUMN_MESSAGE = 'message';
    public const string COLUMN_LINK = 'link';
    public const string COLUMN_NOTIFICATION_CREATED_AT = 'notification_created_at';

    private const string JOINED_COLUMNS = 'notification_recipient.*, '
        . 'notification.type, notification.title, notification.message, notification.link, '
        . 'notification.created_at AS notification_created_at';


    public function __construct(
        private readonly ExplorerNotificationRecipientMapper $mapper,
    ) {
        parent::__construct(self::TABLE_NAME);
    }


    /** @param int[] $userIds */
    public function createMany(int $notificationId, array $userIds): void
    {
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');

        foreach ($userIds as $userId) {
            $this->getTable()->insert([
                self::COLUMN_NOTIFICATION_ID => $notificationId,
                self::COLUMN_USER_ID         => $userId,
                self::COLUMN_CREATED_AT      => $now,
            ]);
        }
    }


    public function countUnreadForUser(int $userId): int
    {
        return $this->getTable()
            ->where(self::COLUMN_USER_ID, $userId)
            ->where(self::COLUMN_READ_AT . ' IS NULL')
            ->where(self::COLUMN_HIDDEN_AT . ' IS NULL')
            ->count('*');
    }


    /** @return UserNotification[] */
    public function findRecentForUser(int $userId, int $limit): array
    {
        $rows = $this->getTable()
            ->select(self::JOINED_COLUMNS)
            ->where(self::COLUMN_USER_ID, $userId)
            ->where(self::COLUMN_HIDDEN_AT . ' IS NULL')
            ->order('notification.created_at DESC')
            ->limit(max(0, $limit));

        return array_values(array_map(
            fn(ActiveRow $row): UserNotification => $this->mapper->mapUserNotification($row),
            iterator_to_array($rows),
        ));
    }


    /** @return Selection<ActiveRow> */
    public function getAllSelectionForUser(int $userId): Selection
    {
        return $this->getTable()
            ->select(self::JOINED_COLUMNS)
            ->where(self::COLUMN_USER_ID, $userId)
            ->where(self::COLUMN_HIDDEN_AT . ' IS NULL')
            ->order('notification.created_at DESC');
    }


    /** Označí přečtené a vrátí odkaz notifikace (pro případné přesměrování), null pokud záznam neexistuje/nepatří uživateli. */
    public function markAsRead(int $recipientId, int $userId): ?string
    {
        $row = $this->getTable()
            ->select(self::JOINED_COLUMNS)
            ->where(self::COLUMN_ID, $recipientId)
            ->where(self::COLUMN_USER_ID, $userId)
            ->fetch();

        if ($row === null) {
            return null;
        }

        $row->update([
            self::COLUMN_READ_AT => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);

        return $row[self::COLUMN_LINK];
    }


    public function markAllAsReadForUser(int $userId): void
    {
        $this->getTable()
            ->where(self::COLUMN_USER_ID, $userId)
            ->where(self::COLUMN_READ_AT . ' IS NULL')
            ->update([
                self::COLUMN_READ_AT => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            ]);
    }


    public function hide(int $recipientId, int $userId): void
    {
        $this->getTable()
            ->where(self::COLUMN_ID, $recipientId)
            ->where(self::COLUMN_USER_ID, $userId)
            ->update([
                self::COLUMN_HIDDEN_AT => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            ]);
    }


    public function deleteOlderThan(DateTimeImmutable $cutoff): int
    {
        return $this->getTable()
            ->where('(' . self::COLUMN_READ_AT . ' IS NOT NULL OR ' . self::COLUMN_HIDDEN_AT . ' IS NOT NULL)')
            ->where(self::COLUMN_CREATED_AT . ' < ?', $cutoff->format('Y-m-d H:i:s'))
            ->delete();
    }
}
