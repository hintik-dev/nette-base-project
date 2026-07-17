<?php declare(strict_types=1);

namespace App\Scheduler;

use App\Domain\ScheduledJob\ExplorerScheduledJobRepository;
use App\Domain\ScheduledJob\ExplorerScheduledJobRunRepository;
use App\Domain\ScheduledJob\ScheduledJobRunTrigger;
use App\Domain\ScheduledJob\ScheduledJobService;
use Contributte\Scheduler\IJob;
use Contributte\Scheduler\IScheduler;
use Cron\CronExpression;
use Nette\DI\Container;
use Tracy\Debugger;

class DatabaseScheduler implements IScheduler
{
    public function __construct(
        private readonly ExplorerScheduledJobRepository $repository,
        private readonly ExplorerScheduledJobRunRepository $runRepository,
        private readonly ScheduledJobService $jobService,
        private readonly Container $container,
    ) {
    }


    /**
     * Hlavní vstupní bod volaný příkazem scheduler:run (každou minutu přes cron).
     * 1. plan()    — pro každou aktivní úlohu s platným cron výrazem vytvoří scheduled záznam
     * 2. execute() — zpracuje všechny scheduled záznamy (running → completed/failed)
     */
    public function run(): void
    {
        $this->plan();
        $this->execute();
    }


    /**
     * PLAN — Zkontroluje aktivní úlohy a pro každou "due" vytvoří záznam se stavem scheduled.
     * Pokud úloha již má aktivní (scheduled/running) záznam, přeskočí ji (ochrana proti duplikátům).
     */
    private function plan(): void
    {
        $now = new \DateTime();

        foreach ($this->repository->findAllActive() as $record) {
            $expression = new CronExpression($record->cron);

            if (!$expression->isDue($now)) {
                continue;
            }

            if ($this->runRepository->hasActiveRunForJob($record->id)) {
                continue;
            }

            $this->runRepository->createScheduled($record->id, ScheduledJobRunTrigger::Scheduler, new \DateTimeImmutable());
        }
    }


    /**
     * EXECUTE — Zpracuje všechny čekající scheduled záznamy (FIFO).
     * Zahrnuje jak cron-triggered, tak manuálně naplánované běhy ("Spustit hned").
     */
    private function execute(): void
    {
        foreach ($this->runRepository->findAllPending() as $run) {
            try {
                $this->jobService->executeRun($run->scheduledJobId, $run->id);
            } catch (\Throwable $e) {
                Debugger::log("Scheduler: run #{$run->id} for job #{$run->scheduledJobId} failed: {$e->getMessage()}", 'scheduler');
            }
        }
    }


    /** @return IJob[] */
    public function getAll(): array
    {
        $jobsByClass = $this->loadJobsByClass();
        $result = [];

        foreach ($this->repository->findAllMapped() as $record) {
            $job = $jobsByClass[$record->class] ?? null;

            if ($job === null) {
                continue;
            }

            $result[$record->id] = new DbJobWrapper($record->cron, $job);
        }

        return $result;
    }


    public function get(string|int $key): ?IJob
    {
        $jobsByClass = $this->loadJobsByClass();

        foreach ($this->repository->findAllMapped() as $record) {
            if ((string) $record->id === (string) $key || $record->name === $key) {
                $job = $jobsByClass[$record->class] ?? null;
                return $job !== null ? new DbJobWrapper($record->cron, $job) : null;
            }
        }

        return null;
    }


    /** Ignored — jobs are managed via the database. */
    public function add(IJob $job, string|int|null $key = null): void
    {
    }


    /** Ignored — jobs are managed via the database. */
    public function remove(string|int $key): void
    {
    }


    /** Ignored — jobs are managed via the database. */
    public function removeAll(): void
    {
    }


    /** @return array<string, IScheduledJob> */
    private function loadJobsByClass(): array
    {
        $result = [];

        foreach ($this->container->findByType(IScheduledJob::class) as $serviceName) {
            $job = $this->container->getService($serviceName);
            $result[$job::class] = $job;
        }

        return $result;
    }
}
