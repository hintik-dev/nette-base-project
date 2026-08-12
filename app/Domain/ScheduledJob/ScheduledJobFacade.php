<?php declare(strict_types=1);

namespace App\Domain\ScheduledJob;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use App\Model\Security\Authorizator\InsufficientPrivilegesException;
use App\Model\Security\Permission\PermissionDefinition;
use App\Model\Security\SecurityUser;

class ScheduledJobFacade
{
    public function __construct(
        private readonly ScheduledJobService $service,
        private readonly SecurityUser $securityUser,
    ) {
    }


    /** @return Selection<ActiveRow> */
    public function getAllDataSource(): Selection
    {
        $this->assertAllowed(ScheduledJobPermission::ListAll);

        return $this->service->getAllDataSource();
    }


    /** @return Selection<ActiveRow> */
    public function getAllRunsDataSource(): Selection
    {
        $this->assertAllowed(ScheduledJobPermission::RunHistory);

        return $this->service->getAllRunsDataSource();
    }


    /** @return Selection<ActiveRow> */
    public function getRunsDataSourceForJob(int $scheduledJobId): Selection
    {
        $this->assertAllowed(ScheduledJobPermission::RunHistory);

        return $this->service->getRunsDataSourceForJob($scheduledJobId);
    }


    public function getById(int $id): ScheduledJob
    {
        $this->assertAllowed(ScheduledJobPermission::ListAll);

        return $this->service->getById($id);
    }


    public function getRunById(int $id): ScheduledJobRun
    {
        $this->assertAllowed(ScheduledJobPermission::RunHistory);

        return $this->service->getRunById($id);
    }


    /** @return Selection<ActiveRow> */
    public function getOutputDataSourceForRun(int $runId): Selection
    {
        $this->assertAllowed(ScheduledJobPermission::RunHistory);

        return $this->service->getOutputDataSourceForRun($runId);
    }


    public function setActive(int $id, bool $active): void
    {
        $this->assertAllowed(ScheduledJobPermission::Edit);

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
        $this->assertAllowed(ScheduledJobPermission::RunNow);

        $this->service->scheduleRun($id, ScheduledJobRunTrigger::Manual);
    }


    /**
     * @throws InsufficientPrivilegesException
     */
    private function assertAllowed(PermissionDefinition $permission): void
    {
        if (!$this->securityUser->isAllowed($permission)) {
            throw new InsufficientPrivilegesException();
        }
    }
}
