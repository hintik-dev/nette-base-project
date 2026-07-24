<?php declare(strict_types=1);

namespace App\Domain\Email\Mail;

class RawMail implements Mail
{
    public function __construct(
        private readonly string $subject,
        private readonly string $bodyHtml,
    ) {
    }


    public function getSubject(): string
    {
        return $this->subject;
    }


    public function getBodyHtml(): string
    {
        return $this->bodyHtml;
    }
}
