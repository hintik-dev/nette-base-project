<?php declare(strict_types=1);

namespace App\Domain\Email;

use App\Domain\Email\Mail\Mail;

/**
 * Veřejný vstupní bod pro zařazení e-mailu do fronty k asynchronnímu odeslání.
 * Skutečné odeslání provádí worker (viz App\Scheduler\ProcessMailQueueJob).
 */
class MailQueueService
{
    public function __construct(
        private readonly ExplorerMailQueueRepository $repository,
    ) {
    }


    public function enqueue(string $recipient, Mail $mail, int $priority = 0): int
    {
        return $this->repository->enqueue($recipient, $mail, $priority);
    }
}
