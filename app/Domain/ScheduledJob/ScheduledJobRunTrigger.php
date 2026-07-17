<?php declare(strict_types=1);

namespace App\Domain\ScheduledJob;

enum ScheduledJobRunTrigger: string
{
    case Scheduler = 'scheduler';
    case Manual = 'manual';

    public function toLabel(): string
    {
        return match($this) {
            self::Scheduler => 'Plánovač',
            self::Manual    => 'Ručně',
        };
    }
}
