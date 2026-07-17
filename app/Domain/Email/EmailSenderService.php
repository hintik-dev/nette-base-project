<?php declare(strict_types=1);

namespace App\Domain\Email;

use App\Domain\Email\Mail\Mail;
use Nette\Mail\Mailer;
use Nette\Mail\Message;

class EmailSenderService
{
    public function __construct(
        private readonly Mailer $mailer,
        private readonly ExplorerSentEmailRepository $repository,
        private readonly string $fromAddress,
        private readonly string $fromName,
    ) {
    }


    public function send(string $recipient, Mail $mail): void
    {
        $id = $this->repository->insert($recipient, $mail->getSubject(), $mail->getBodyHtml());

        try {
            $message = new Message();
            $message->setFrom($this->fromAddress, $this->fromName);
            $message->addTo($recipient);
            $message->setSubject($mail->getSubject());
            $message->setHtmlBody($mail->getBodyHtml());

            $this->mailer->send($message);
            $this->repository->markSent($id);
        } catch (\Throwable $e) {
            $this->repository->markFailed($id, $e->getMessage());
            throw $e;
        }
    }
}
