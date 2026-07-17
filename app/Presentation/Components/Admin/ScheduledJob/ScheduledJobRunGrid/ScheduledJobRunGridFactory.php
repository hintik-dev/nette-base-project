<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\ScheduledJob\ScheduledJobRunGrid;

interface ScheduledJobRunGridFactory
{
    public function create(?int $scheduledJobId): ScheduledJobRunGrid;
}
