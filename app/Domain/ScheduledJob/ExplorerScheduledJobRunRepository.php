<?php declare(strict_types=1);

namespace App\Domain\ScheduledJob;

use App\Core\Database\SchedulerExplorerRepository;
use Nette\Database\Table\Selection;

class ExplorerScheduledJobRunRepository extends SchedulerExplorerRepository
{
    public const string COLUMN_ID = 'id';
    public const string COLUMN_SCHEDULED_JOB_ID = 'scheduled_job_id';
    public const string COLUMN_STATUS = 'status';
    public const string COLUMN_TRIGGER = 'trigger';
    public const string COLUMN_STARTED_AT = 'started_at';
    public const string COLUMN_FINISHED_AT = 'finished_at';
    public const string COLUMN_DURATION_MS = 'duration_ms';

    public const string TABLE_NAME = 'scheduled_job_run';


    public function __construct(
        private readonly ExplorerScheduledJobRunMapper $mapper,
    ) {
        parent::__construct(self::TABLE_NAME);
    }


    public function getById(int $id): ScheduledJobRun
    {
        $row = $this->find($id);

        if ($row === null) {
            throw new ScheduledJobRunNotFoundException($id);
        }

        return $this->mapper->mapScheduledJobRun($row);
    }


    /** Vytvoří záznam se stavem "naplánováno" (ještě nespuštěno). */
    public function createScheduled(
        int $scheduledJobId,
        ScheduledJobRunTrigger $trigger,
        \DateTimeImmutable $scheduledAt,
    ): int {
        $row = $this->getTable()->insert([
            self::COLUMN_SCHEDULED_JOB_ID => $scheduledJobId,
            self::COLUMN_STATUS           => ScheduledJobRunStatus::Scheduled->value,
            self::COLUMN_TRIGGER          => $trigger->value,
            self::COLUMN_STARTED_AT       => $scheduledAt->format('Y-m-d H:i:s'),
        ]);

        assert($row instanceof \Nette\Database\Table\ActiveRow);

        return (int) $row[self::COLUMN_ID];
    }


    /** Označí záznam jako běžící (přechod scheduled → running). */
    public function markRunning(int $runId): void
    {
        $this->getTable()
            ->where(self::COLUMN_ID, $runId)
            ->update([
                self::COLUMN_STATUS => ScheduledJobRunStatus::Running->value,
            ]);
    }


    public function finishSuccess(int $runId, \DateTimeImmutable $finishedAt, int $durationMs): void
    {
        $this->getTable()
            ->where(self::COLUMN_ID, $runId)
            ->update([
                self::COLUMN_STATUS      => ScheduledJobRunStatus::Completed->value,
                self::COLUMN_FINISHED_AT => $finishedAt->format('Y-m-d H:i:s'),
                self::COLUMN_DURATION_MS => $durationMs,
            ]);
    }


    public function finishError(int $runId, \DateTimeImmutable $finishedAt, int $durationMs): void
    {
        $this->getTable()
            ->where(self::COLUMN_ID, $runId)
            ->update([
                self::COLUMN_STATUS      => ScheduledJobRunStatus::Failed->value,
                self::COLUMN_FINISHED_AT => $finishedAt->format('Y-m-d H:i:s'),
                self::COLUMN_DURATION_MS => $durationMs,
            ]);
    }


    /**
     * Všechny čekající záznamy napříč všemi úlohami, seřazené FIFO.
     * @return ScheduledJobRun[]
     */
    public function findAllPending(): array
    {
        return array_map(
            fn($row) => $this->mapper->mapScheduledJobRun($row),
            iterator_to_array(
                $this->getTable()
                    ->where(self::COLUMN_STATUS, ScheduledJobRunStatus::Scheduled->value)
                    ->order(self::COLUMN_STARTED_AT . ' ASC'),
            ),
        );
    }


    /**
     * Všechny chybové běhy dokončené od zadaného okamžiku, seřazené od nejnovějšího.
     * @return ScheduledJobRun[]
     */
    public function findFailedSince(\DateTimeImmutable $since): array
    {
        return array_map(
            fn($row) => $this->mapper->mapScheduledJobRun($row),
            iterator_to_array(
                $this->getTable()
                    ->where(self::COLUMN_STATUS, ScheduledJobRunStatus::Failed->value)
                    ->where(self::COLUMN_FINISHED_AT . ' >= ?', $since->format('Y-m-d H:i:s'))
                    ->order(self::COLUMN_FINISHED_AT . ' DESC'),
            ),
        );
    }


    /**
     * Vrátí true pokud má úloha již záznam ve stavu scheduled nebo running.
     * Slouží jako ochrana proti duplicitnímu plánování.
     */
    public function hasActiveRunForJob(int $scheduledJobId): bool
    {
        return $this->getTable()
            ->where(self::COLUMN_SCHEDULED_JOB_ID, $scheduledJobId)
            ->where(self::COLUMN_STATUS . ' IN (?)', [
                ScheduledJobRunStatus::Scheduled->value,
                ScheduledJobRunStatus::Running->value,
            ])
            ->count('*') > 0;
    }


    /** @return Selection<\Nette\Database\Table\ActiveRow> */
    public function getAllSelection(): Selection
    {
        return parent::findAll()->order(self::COLUMN_STARTED_AT . ' DESC');
    }


    /** @return Selection<\Nette\Database\Table\ActiveRow> */
    public function getSelectionForJob(int $scheduledJobId): Selection
    {
        return $this->getTable()
            ->where(self::COLUMN_SCHEDULED_JOB_ID, $scheduledJobId)
            ->order(self::COLUMN_STARTED_AT . ' DESC');
    }


    public function findLastFinishedAt(int $scheduledJobId): ?\DateTimeImmutable
    {
        $row = $this->getTable()
            ->where(self::COLUMN_SCHEDULED_JOB_ID, $scheduledJobId)
            ->where(self::COLUMN_STATUS, ScheduledJobRunStatus::Completed->value)
            ->order(self::COLUMN_FINISHED_AT . ' DESC')
            ->fetch();

        if ($row === null || $row[self::COLUMN_FINISHED_AT] === null) {
            return null;
        }

        return \DateTimeImmutable::createFromMutable(
            \Nette\Utils\DateTime::from($row[self::COLUMN_FINISHED_AT]),
        );
    }
}
