<?php declare(strict_types=1);

namespace App\Domain\Email\Mail;

abstract class BaseMail implements Mail
{
    /**
     * Vrátí HTML obsah mailu bez obálky.
     * Výsledek je automaticky zabalen do společného layoutu v getBodyHtml().
     */
    abstract protected function getContent(): string;


    final public function getBodyHtml(): string
    {
        $subject = htmlspecialchars($this->getSubject());
        $content = $this->getContent();
        $year = date('Y');

        return <<<HTML
        <!DOCTYPE html>
        <html lang="cs">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{$subject}</title>
        </head>
        <body style="margin:0;padding:0;background:#f0f2f5;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#333;">

        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f0f2f5;padding:32px 16px;">
        <tr><td align="center">
        <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:10px;overflow:hidden;">

            <!-- SUBJECT BAR -->
            <tr>
                <td style="background:#0d6efd;padding:24px 32px;">
                    <h1 style="margin:0;font-size:18px;font-weight:bold;color:#ffffff;line-height:1.3;">{$subject}</h1>
                </td>
            </tr>

            <!-- CONTENT -->
            <tr>
                <td style="padding:28px 32px;">
                    {$content}
                </td>
            </tr>

            <!-- FOOTER -->
            <tr>
                <td style="background:#f8f9fa;border-top:1px solid #e8e8e8;padding:20px 32px;">
                    <p style="margin:0;font-size:11px;color:#999;line-height:1.5;">
                        Tento e-mail byl vygenerován automaticky. Neodpovídejte na tuto zprávu.<br>
                        &copy; {$year}
                    </p>
                </td>
            </tr>

        </table>
        </td></tr>
        </table>

        </body>
        </html>
        HTML;
    }
}
