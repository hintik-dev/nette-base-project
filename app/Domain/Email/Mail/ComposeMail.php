<?php declare(strict_types=1);

namespace App\Domain\Email\Mail;

class ComposeMail extends BaseMail
{
    public function __construct(
        private readonly string $subject,
        private readonly string $content,
    ) {
    }


    public function getSubject(): string
    {
        return $this->subject;
    }


    protected function getContent(): string
    {
        return $this->content;
    }
}
