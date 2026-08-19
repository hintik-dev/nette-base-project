<?php declare(strict_types=1);

namespace App\Domain\Email\Mail;

class PasswordResetMail extends BaseMail
{
    public function __construct(
        private readonly string $resetUrl,
    ) {
    }


    public function getSubject(): string
    {
        return 'Obnova zapomenutého hesla';
    }


    public function isSensitive(): bool
    {
        return true;
    }


    protected function getContent(): string
    {
        $url = htmlspecialchars($this->resetUrl);

        $buttonStyle = 'display:inline-block;padding:10px 20px;background:#0d6efd;'
            . 'color:#ffffff;text-decoration:none;border-radius:6px;';

        return <<<HTML
        <p>Byla zaznamenána žádost o obnovu hesla k vašemu účtu.</p>
        <p><a href="{$url}" style="{$buttonStyle}">Nastavit nové heslo</a></p>
        <p>Pokud tlačítko nefunguje, zkopírujte tento odkaz do prohlížeče:<br>{$url}</p>
        <p style="color:#999;font-size:12px;">
            Odkaz je platný 1 hodinu. Pokud jste o obnovu hesla nežádali,
            tento e-mail ignorujte — heslo zůstane beze změny.
        </p>
        HTML;
    }
}
