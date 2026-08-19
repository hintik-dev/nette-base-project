<?php declare(strict_types=1);

namespace App\Domain\Email;

use DateTimeImmutable;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;

class ExplorerMailQueueMapper
{
    public function mapMailQueue(ActiveRow $row): MailQueue
    {
        return new MailQueue(
            id: $row[ExplorerMailQueueRepository::COLUMN_ID],
            recipient: $row[ExplorerMailQueueRepository::COLUMN_RECIPIENT],
            subject: $row[ExplorerMailQueueRepository::COLUMN_SUBJECT],
            bodyHtml: $row[ExplorerMailQueueRepository::COLUMN_BODY_HTML],
            mailClass: $row[ExplorerMailQueueRepository::COLUMN_MAIL_CLASS],
            isSensitive: (bool) $row[ExplorerMailQueueRepository::COLUMN_IS_SENSITIVE],
            priority: (int) $row[ExplorerMailQueueRepository::COLUMN_PRIORITY],
            status: MailQueueStatus::from($row[ExplorerMailQueueRepository::COLUMN_STATUS]),
            attempts: (int) $row[ExplorerMailQueueRepository::COLUMN_ATTEMPTS],
            maxAttempts: (int) $row[ExplorerMailQueueRepository::COLUMN_MAX_ATTEMPTS],
            nextAttemptAt: $row[ExplorerMailQueueRepository::COLUMN_NEXT_ATTEMPT_AT] !== null
                ? DateTimeImmutable::createFromMutable(DateTime::from($row[ExplorerMailQueueRepository::COLUMN_NEXT_ATTEMPT_AT]))
                : null,
            lockedAt: $row[ExplorerMailQueueRepository::COLUMN_LOCKED_AT] !== null
                ? DateTimeImmutable::createFromMutable(DateTime::from($row[ExplorerMailQueueRepository::COLUMN_LOCKED_AT]))
                : null,
            lockedBy: $row[ExplorerMailQueueRepository::COLUMN_LOCKED_BY],
            error: $row[ExplorerMailQueueRepository::COLUMN_ERROR],
            createdAt: DateTimeImmutable::createFromMutable(
                DateTime::from($row[ExplorerMailQueueRepository::COLUMN_CREATED_AT]),
            ),
        );
    }
}
