<?php declare(strict_types=1);

namespace App\Scheduler;

use Contributte\Scheduler\ExpressionJob;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Obalí IScheduledJob jako IJob (contributte), aby fungovaly CLI příkazy scheduler:list, scheduler:force-run.
 * Výstup z run() je při CLI volání zahazován (NullOutput).
 */
final class DbJobWrapper extends ExpressionJob
{
    public function __construct(string $cron, private readonly IScheduledJob $inner)
    {
        parent::__construct($cron);
    }


    public function run(): void
    {
        $this->inner->run(new NullOutput());
    }
}
