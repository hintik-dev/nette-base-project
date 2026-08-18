<?php declare(strict_types=1);

namespace App\Domain\Email;

use DateTimeImmutable;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Zpracovává frontu mail_queue. Volá se výhradně z App\Scheduler\ProcessMailQueueJob (worker).
 */
class MailQueueProcessorService
{
    /** Backoff v minutách indexovaný počtem pokusů (1. selhání, 2. selhání, ...), poslední hodnota se opakuje. */
    private const array RETRY_BACKOFF_MINUTES = [1, 5, 15, 30, 60];

    public function __construct(
        private readonly ExplorerMailQueueRepository $queueRepository,
        private readonly ExplorerSentEmailRepository $sentEmailRepository,
        private readonly EmailSenderService $emailSenderService,
        private readonly int $batchSize = 20,
    ) {
    }


    public function processBatch(OutputInterface $output): void
    {
        $workerId = uniqid('mail-queue-', true);
        $items = $this->queueRepository->claimBatch($this->batchSize, $workerId);

        if ($items === []) {
            $output->writeln('Fronta je prázdná, není co zpracovat.');
            return;
        }

        $sent = 0;
        $retried = 0;
        $failed = 0;

        foreach ($items as $item) {
            try {
                $this->emailSenderService->send($item->recipient, $item->subject, $item->bodyHtml);
                $this->archiveResolved($item, SentEmailStatus::Sent, null);
                $sent++;
            } catch (Throwable $e) {
                $attempts = $item->attempts + 1;

                if ($attempts < $item->maxAttempts) {
                    $this->queueRepository->retryLater($item->id, $e->getMessage(), $this->nextAttemptAt($attempts), $attempts);
                    $retried++;
                } else {
                    $this->archiveResolved($item, SentEmailStatus::Failed, $e->getMessage());
                    $failed++;
                }
            }
        }

        $output->writeln(sprintf(
            'Zpracováno %d položek: %d odesláno, %d dočasně selhalo (bude opakováno), %d trvale selhalo.',
            count($items),
            $sent,
            $retried,
            $failed,
        ));
    }


    private function archiveResolved(MailQueue $item, SentEmailStatus $status, ?string $error): void
    {
        $this->queueRepository->transaction(function () use ($item, $status, $error): void {
            $this->sentEmailRepository->insertResolved(
                recipient: $item->recipient,
                subject: $item->subject,
                bodyHtml: $item->isSensitive ? null : $item->bodyHtml,
                status: $status,
                error: $error,
                mailClass: $item->mailClass,
                isSensitive: $item->isSensitive,
                sentAt: $status === SentEmailStatus::Sent ? new DateTimeImmutable() : null,
            );
            $this->queueRepository->delete($item->id);
        });
    }


    private function nextAttemptAt(int $attempts): DateTimeImmutable
    {
        $index = min($attempts - 1, count(self::RETRY_BACKOFF_MINUTES) - 1);
        $minutes = self::RETRY_BACKOFF_MINUTES[$index];

        return (new DateTimeImmutable())->modify("+{$minutes} minutes");
    }
}
