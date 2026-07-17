<?php declare(strict_types=1);

namespace App\Domain\ScheduledJob;

use App\Core\Database\SchedulerExplorerRepository;
use Nette\Database\Table\Selection;

class ExplorerScheduledJobRunOutputRepository extends SchedulerExplorerRepository
{
    public const string COLUMN_ID = 'id';
    public const string COLUMN_SCHEDULED_JOB_RUN_ID = 'scheduled_job_run_id';
    public const string COLUMN_CREATED_AT = 'created_at';
    public const string COLUMN_MESSAGE = 'message';

    public const string TABLE_NAME = 'scheduled_job_run_output';


    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }


    /**
     * @param array{message: string, createdAt: \DateTimeImmutable}[] $entries
     */
    public function insertMany(int $runId, array $entries): void
    {
        foreach ($entries as $entry) {
            $this->getTable()->insert([
                self::COLUMN_SCHEDULED_JOB_RUN_ID => $runId,
                self::COLUMN_CREATED_AT           => $entry['createdAt']->format('Y-m-d H:i:s.u'),
                self::COLUMN_MESSAGE              => $entry['message'],
            ]);
        }
    }


    /** @return Selection<\Nette\Database\Table\ActiveRow> */
    public function getSelectionForRun(int $runId): Selection
    {
        return $this->getTable()
            ->where(self::COLUMN_SCHEDULED_JOB_RUN_ID, $runId)
            ->order(self::COLUMN_ID . ' ASC');
    }
}
