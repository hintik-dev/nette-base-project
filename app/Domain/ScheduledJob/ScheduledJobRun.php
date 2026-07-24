<?php declare(strict_types=1);

namespace App\Domain\ScheduledJob;

readonly class ScheduledJobRun
{
    public function __construct(
        public int $id,
        public int $scheduledJobId,
        public ScheduledJobRunStatus $status,
        public ScheduledJobRunTrigger $trigger,
        public \DateTimeImmutable $startedAt,
        public ?\DateTimeImmutable $finishedAt,
        public ?int $durationMs,
    ) {
    }
}
