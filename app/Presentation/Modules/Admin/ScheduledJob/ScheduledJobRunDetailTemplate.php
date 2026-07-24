<?php declare(strict_types=1);
namespace App\Presentation\Modules\Admin\ScheduledJob;

use App\Domain\ScheduledJob\ScheduledJob;
use App\Domain\ScheduledJob\ScheduledJobRun;
use App\Model\Latte\BaseTemplate;

final class ScheduledJobRunDetailTemplate extends BaseTemplate
{
    public ScheduledJob $job;

    public ScheduledJobRun $run;
}
