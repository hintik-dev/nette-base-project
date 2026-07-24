<?php declare(strict_types=1);

namespace App\Domain\Email\Mail;

interface Mail
{
    public function getSubject(): string;

    public function getBodyHtml(): string;
}
