<?php declare(strict_types=1);

namespace App\Scheduler;

use Symfony\Component\Console\Output\OutputInterface;

class HelloWorldJob extends BaseJob
{
    public function run(OutputInterface $output): void
    {
        $output->writeln('Hello World! Úloha spuštěna v ' . date('Y-m-d H:i:s'));
    }
}
