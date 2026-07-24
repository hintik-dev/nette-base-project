<?php declare(strict_types=1);
namespace App\Presentation\Modules\Admin\ScheduledJob;

use App\Domain\ScheduledJob\ScheduledJob;
use App\Model\Latte\BaseTemplate;

final class ScheduledJobRunsTemplate extends BaseTemplate
{
    public ?ScheduledJob $job = null;
}
