<?php declare(strict_types=1);

namespace App\Domain\ScheduledJob;

readonly class ScheduledJob
{
    public function __construct(
        public int $id,
        public string $name,
        public string $class,
        public ?string $description,
        public string $cron,
        public bool $isActive,
        public \DateTimeImmutable $createdAt,
    ) {
    }

    public function getShortClass(): string
    {
        $parts = explode('\\', $this->class);
        return end($parts);
    }
}
