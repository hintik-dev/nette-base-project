<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\ScheduledJob\ScheduledJobRunOutputGrid;

interface ScheduledJobRunOutputGridFactory
{
    public function create(int $runId): ScheduledJobRunOutputGrid;
}
