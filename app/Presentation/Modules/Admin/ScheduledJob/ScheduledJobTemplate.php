<?php declare(strict_types=1);
namespace App\Presentation\Modules\Admin\ScheduledJob;

use App\Domain\ScheduledJob\ScheduledJob;
use App\Domain\ScheduledJob\ScheduledJobRun;
use App\Model\Latte\BaseTemplate;

final class ScheduledJobTemplate extends BaseTemplate
{
    public ?ScheduledJob $job = null;

    public ?ScheduledJobRun $run = null;
}
