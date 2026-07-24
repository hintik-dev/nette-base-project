<?php declare(strict_types=1);

namespace App\Domain\ScheduledJob;

readonly class ScheduledJobRunOutput
{
    public function __construct(
        public int $id,
        public int $scheduledJobRunId,
        public \DateTimeImmutable $createdAt,
        public string $message,
    ) {
    }
}
