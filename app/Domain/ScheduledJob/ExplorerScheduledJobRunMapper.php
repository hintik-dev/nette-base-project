<?php declare(strict_types=1);

namespace App\Domain\ScheduledJob;

use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;

class ExplorerScheduledJobRunMapper
{
    public function mapScheduledJobRun(ActiveRow $row): ScheduledJobRun
    {
        return new ScheduledJobRun(
            id:             $row[ExplorerScheduledJobRunRepository::COLUMN_ID],
            scheduledJobId: $row[ExplorerScheduledJobRunRepository::COLUMN_SCHEDULED_JOB_ID],
            status:         ScheduledJobRunStatus::from($row[ExplorerScheduledJobRunRepository::COLUMN_STATUS]),
            trigger:        ScheduledJobRunTrigger::from($row[ExplorerScheduledJobRunRepository::COLUMN_TRIGGER]),
            startedAt:      \DateTimeImmutable::createFromMutable(
                object: DateTime::from($row[ExplorerScheduledJobRunRepository::COLUMN_STARTED_AT])
            ),
            finishedAt:     $row[ExplorerScheduledJobRunRepository::COLUMN_FINISHED_AT] !== null
                ? \DateTimeImmutable::createFromMutable(DateTime::from($row[ExplorerScheduledJobRunRepository::COLUMN_FINISHED_AT]))
                : null,
            durationMs:     $row[ExplorerScheduledJobRunRepository::COLUMN_DURATION_MS],
        );
    }
}
