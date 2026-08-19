<?php declare(strict_types=1);

namespace App\Domain\Notification;

use App\Core\Database\ExplorerRepository;
use DateTimeImmutable;
use Nette\Database\Table\ActiveRow;

class ExplorerNotificationRepository extends ExplorerRepository
{
    public const string TABLE_NAME = 'notification';

    public const string COLUMN_ID = 'id';
    public const string COLUMN_TYPE = 'type';
    public const string COLUMN_TITLE = 'title';
    public const string COLUMN_MESSAGE = 'message';
    public const string COLUMN_LINK = 'link';
    public const string COLUMN_CREATED_AT = 'created_at';


    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }


    public function create(NotificationType $type, string $title, string $message, ?string $link): int
    {
        $row = $this->getTable()->insert([
            self::COLUMN_TYPE       => $type->value,
            self::COLUMN_TITLE      => $title,
            self::COLUMN_MESSAGE    => $message,
            self::COLUMN_LINK       => $link,
            self::COLUMN_CREATED_AT => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);

        assert($row instanceof ActiveRow);

        return (int) $row[self::COLUMN_ID];
    }
}
