<?php declare(strict_types=1);

namespace App\Domain\ScheduledJob;

use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;

class ExplorerScheduledJobRunOutputMapper
{
    public function mapOutput(ActiveRow $row): ScheduledJobRunOutput
    {
        return new ScheduledJobRunOutput(
            id:                  $row[ExplorerScheduledJobRunOutputRepository::COLUMN_ID],
            scheduledJobRunId:   $row[ExplorerScheduledJobRunOutputRepository::COLUMN_SCHEDULED_JOB_RUN_ID],
            createdAt:           \DateTimeImmutable::createFromMutable(
                object: DateTime::from($row[ExplorerScheduledJobRunOutputRepository::COLUMN_CREATED_AT])
            ),
            message:             $row[ExplorerScheduledJobRunOutputRepository::COLUMN_MESSAGE],
        );
    }
}
