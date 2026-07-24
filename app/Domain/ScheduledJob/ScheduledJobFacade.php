<?php declare(strict_types=1);

namespace App\Domain\ScheduledJob;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class ScheduledJobFacade
{
    public function __construct(
        private readonly ScheduledJobService $service,
    ) {
    }


    /** @return Selection<ActiveRow> */
    public function getAllDataSource(): Selection
    {
        return $this->service->getAllDataSource();
    }


    /** @return Selection<ActiveRow> */
    public function getAllRunsDataSource(): Selection
    {
        return $this->service->getAllRunsDataSource();
    }


    /** @return Selection<ActiveRow> */
    public function getRunsDataSourceForJob(int $scheduledJobId): Selection
    {
        return $this->service->getRunsDataSourceForJob($scheduledJobId);
    }


    public function getById(int $id): ScheduledJob
    {
        return $this->service->getById($id);
    }


    public function getRunById(int $id): ScheduledJobRun
    {
        return $this->service->getRunById($id);
    }


    /** @return Selection<ActiveRow> */
    public function getOutputDataSourceForRun(int $runId): Selection
    {
        return $this->service->getOutputDataSourceForRun($runId);
    }


    public function setActive(int $id, bool $active): void
    {
        $this->service->setActive($id, $active);
    }


    public function getLastRunAt(int $jobId): ?\DateTimeImmutable
    {
        return $this->service->getLastRunAt($jobId);
    }


    public function getLastRunAtForClass(string $class): ?\DateTimeImmutable
    {
        return $this->service->getLastRunAtForClass($class);
    }


    /** @return FailedJobRunSummary[] */
    public function findFailedRunSummariesSince(\DateTimeImmutable $since): array
    {
        return $this->service->findFailedRunSummariesSince($since);
    }


    public function runNow(int $id): void
    {
        $this->service->scheduleRun($id, ScheduledJobRunTrigger::Manual);
    }
}
