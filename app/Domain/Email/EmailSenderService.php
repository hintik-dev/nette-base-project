<?php declare(strict_types=1);

namespace App\Domain\Email;

use Nette\Mail\Mailer;
use Nette\Mail\Message;

/**
 * Čistý SMTP transport — sestaví a odešle zprávu, bez jakékoliv perzistence.
 * Volá se výhradně z workeru (MailQueueProcessorService), který řeší frontu, retry a log.
 */
class EmailSenderService
{
    public function __construct(
        private readonly Mailer $mailer,
        private readonly string $fromAddress,
        private readonly string $fromName,
    ) {
    }


    public function send(string $recipient, string $subject, string $bodyHtml): void
    {
        $message = new Message();
        $message->setFrom($this->fromAddress, $this->fromName);
        $message->addTo($recipient);
        $message->setSubject($subject);
        $message->setHtmlBody($bodyHtml);

        $this->mailer->send($message);
    }
}
