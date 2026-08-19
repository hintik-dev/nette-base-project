<?php declare(strict_types=1);

namespace App\Scheduler;

use App\Domain\Notification\NotificationService;
use DateTimeImmutable;
use Symfony\Component\Console\Output\OutputInterface;

class PruneNotificationsJob extends BaseJob
{
    private const string RETENTION = '-90 days';

    public function __construct(
        private readonly NotificationService $notificationService,
    ) {
    }


    public function run(OutputInterface $output): void
    {
        $cutoff = (new DateTimeImmutable())->modify(self::RETENTION);
        $deleted = $this->notificationService->pruneOld($cutoff);

        $output->writeln(sprintf('Smazáno %d přečtených/skrytých notifikací starších než 90 dní.', $deleted));
    }
}
