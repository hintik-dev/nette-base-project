<?php declare(strict_types=1);

namespace App\Domain\ScheduledJob;

readonly class FailedJobRunSummary
{
    public function __construct(
        public string $jobName,
        public \DateTimeImmutable $finishedAt,
        public ?string $error,
    ) {
    }
}
