<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\ScheduledJob\ScheduledJobGrid;

interface ScheduledJobGridFactory
{
    public function create(): ScheduledJobGrid;
}
