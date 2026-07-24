<?php declare(strict_types=1);

namespace App\Domain\Email;

use DateTimeImmutable;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;

class ExplorerSentEmailMapper
{
    public function mapSentEmail(ActiveRow $row): SentEmail
    {
        return new SentEmail(
            id: $row[ExplorerSentEmailRepository::COLUMN_ID],
            recipient: $row[ExplorerSentEmailRepository::COLUMN_RECIPIENT],
            subject: $row[ExplorerSentEmailRepository::COLUMN_SUBJECT],
            bodyHtml: $row[ExplorerSentEmailRepository::COLUMN_BODY_HTML],
            status: SentEmailStatus::from($row[ExplorerSentEmailRepository::COLUMN_STATUS]),
            error: $row[ExplorerSentEmailRepository::COLUMN_ERROR],
            sentAt: $row[ExplorerSentEmailRepository::COLUMN_SENT_AT] !== null
                ? DateTimeImmutable::createFromMutable(DateTime::from($row[ExplorerSentEmailRepository::COLUMN_SENT_AT]))
                : null,
            createdAt: DateTimeImmutable::createFromMutable(
                DateTime::from($row[ExplorerSentEmailRepository::COLUMN_CREATED_AT]),
            ),
        );
    }
}
