<?php declare(strict_types=1);

namespace App\Domain\ScheduledJob;

use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;

class ExplorerScheduledJobMapper
{
    public function mapScheduledJob(ActiveRow $row): ScheduledJob
    {
        return new ScheduledJob(
            id:          $row[ExplorerScheduledJobRepository::COLUMN_ID],
            name:        $row[ExplorerScheduledJobRepository::COLUMN_NAME],
            class:       $row[ExplorerScheduledJobRepository::COLUMN_CLASS],
            description: $row[ExplorerScheduledJobRepository::COLUMN_DESCRIPTION],
            cron:        $row[ExplorerScheduledJobRepository::COLUMN_CRON],
            isActive:    (bool) $row[ExplorerScheduledJobRepository::COLUMN_IS_ACTIVE],
            createdAt:   \DateTimeImmutable::createFromMutable(DateTime::from($row[ExplorerScheduledJobRepository::COLUMN_CREATED_AT])),
        );
    }
}
