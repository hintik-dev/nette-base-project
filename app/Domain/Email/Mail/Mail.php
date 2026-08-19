<?php declare(strict_types=1);

namespace App\Domain\Email\Mail;

interface Mail
{
    public function getSubject(): string;

    public function getBodyHtml(): string;

    /**
     * Zda mail obsahuje citlivá data (např. token na reset hesla).
     * Pro takové maily se do sent_email nikdy neukládá tělo a nelze je znovu odeslat z historie.
     */
    public function isSensitive(): bool;
}
