<?php declare(strict_types=1);

namespace App\Scheduler;

use App\Domain\Email\MailQueueProcessorService;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessMailQueueJob extends BaseJob
{
    public function __construct(
        private readonly MailQueueProcessorService $processorService,
    ) {
    }


    public function run(OutputInterface $output): void
    {
        $this->processorService->processBatch($output);
    }
}
