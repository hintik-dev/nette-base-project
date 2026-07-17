<?php declare(strict_types=1);

namespace App\Domain\ScheduledJob;

use App\Scheduler\DatabaseOutput;
use App\Scheduler\IScheduledJob;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use Tracy\Debugger;

readonly class ScheduledJobService
{
    public function __construct(
        private ExplorerScheduledJobRepository $repository,
        private ExplorerScheduledJobRunRepository $runRepository,
        private ExplorerScheduledJobRunOutputRepository $outputRepository,
        private Container $container,
    ) {
    }


    /** @return Selection<ActiveRow> */
    public function getAllDataSource(): Selection
    {
        return $this->repository->getAllSelection();
    }


    /** @return Selection<ActiveRow> */
    public function getAllRunsDataSource(): Selection
    {
        return $this->runRepository->getAllSelection();
    }


    /** @return Selection<ActiveRow> */
    public function getRunsDataSourceForJob(int $scheduledJobId): Selection
    {
        return $this->runRepository->getSelectionForJob($scheduledJobId);
    }


    /** @return Selection<ActiveRow> */
    public function getOutputDataSourceForRun(int $runId): Selection
    {
        return $this->outputRepository->getSelectionForRun($runId);
    }


    public function getById(int $id): ScheduledJob
    {
        return $this->repository->getById($id);
    }


    public function getRunById(int $id): ScheduledJobRun
    {
        return $this->runRepository->getById($id);
    }


    public function toggleActive(int $id): void
    {
        $this->repository->toggleActive($id);
    }


    public function getLastRunAt(int $jobId): ?\DateTimeImmutable
    {
        return $this->runRepository->findLastFinishedAt($jobId);
    }


    /** Vrátí čas posledního úspěšného běhu úlohy dané třídy (např. sebe sama). */
    public function getLastRunAtForClass(string $class): ?\DateTimeImmutable
    {
        $job = $this->repository->findByClass($class);

        return $job !== null ? $this->runRepository->findLastFinishedAt($job->id) : null;
    }


    /**
     * Vrátí přehled chybových běhů napříč všemi úlohami od zadaného okamžiku.
     * @return FailedJobRunSummary[]
     */
    public function findFailedRunSummariesSince(\DateTimeImmutable $since): array
    {
        $runs = $this->runRepository->findFailedSince($since);

        if ($runs === []) {
            return [];
        }

        $jobNames = [];
        foreach ($this->repository->findAllMapped() as $job) {
            $jobNames[$job->id] = $job->name;
        }

        return array_map(
            fn(ScheduledJobRun $run) => new FailedJobRunSummary(
                jobName:    $jobNames[$run->scheduledJobId] ?? sprintf('Úloha #%d', $run->scheduledJobId),
                finishedAt: $run->finishedAt ?? $run->startedAt,
            ),
            $runs,
        );
    }


    /** Ruční naplánování běhu (tlačítko "Spustit hned"). */
    public function scheduleRun(int $id, ScheduledJobRunTrigger $trigger): void
    {
        $this->registerRun($id, $trigger);
    }


    /**
     * PLAN — Zaregistruje a naplánuje běh úlohy (voláno DatabaseSchedulerem pro "due" úlohy).
     */
    public function planRun(int $id): void
    {
        $this->registerRun($id, ScheduledJobRunTrigger::Scheduler);
    }


    /** Vytvoří záznam běhu a rovnou zaloguje jeho registraci a naplánování. */
    private function registerRun(int $id, ScheduledJobRunTrigger $trigger): int
    {
        $runId = $this->runRepository->createScheduled($id, $trigger, new \DateTimeImmutable());

        $this->outputRepository->insertMany($runId, [
            ['message' => 'Běh byl zaregistrován.', 'createdAt' => new \DateTimeImmutable()],
            ['message' => 'Běh byl naplánován ke spuštění.', 'createdAt' => new \DateTimeImmutable()],
        ]);

        return $runId;
    }


    /**
     * EXECUTE — Zpracuje konkrétní čekající záznam (scheduled → running → completed/failed).
     * Volá DatabaseScheduler při každém spuštění scheduler:run.
     */
    public function executeRun(int $jobId, int $runId): void
    {
        $record = $this->repository->getById($jobId);
        $job = $this->resolveJobInstance($record->class);

        $this->runRepository->markRunning($runId);
        $this->outputRepository->insertMany($runId, [
            ['message' => 'Běh byl spuštěn.', 'createdAt' => new \DateTimeImmutable()],
        ]);

        $output = new DatabaseOutput();
        $startMs = (int) round(microtime(true) * 1000);

        try {
            $job->run($output);

            $finishedAt = new \DateTimeImmutable();
            $durationMs = (int) round(microtime(true) * 1000) - $startMs;

            $output->writeln('Úloha byla dokončena.');

            $this->runRepository->finishSuccess($runId, $finishedAt, $durationMs);
            $this->outputRepository->insertMany($runId, $output->getEntries());
        } catch (\Throwable $e) {
            $finishedAt = new \DateTimeImmutable();
            $durationMs = (int) round(microtime(true) * 1000) - $startMs;

            $output->writeln('Chyba: ' . $e->getMessage());

            $this->runRepository->finishError($runId, $finishedAt, $durationMs);
            $this->outputRepository->insertMany($runId, $output->getEntries());
            Debugger::log($e, 'scheduler');

            throw $e;
        }
    }


    private function resolveJobInstance(string $class): IScheduledJob
    {
        foreach ($this->container->findByType(IScheduledJob::class) as $serviceName) {
            $service = $this->container->getService($serviceName);
            if ($service::class === $class) {
                return $service;
            }
        }

        throw new \RuntimeException("Job class not found in container: {$class}");
    }
}
