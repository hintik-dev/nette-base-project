<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\Email\ComposeEmailForm;

use App\Domain\Email\Mail\ComposeMail;
use App\Domain\Email\MailQueueService;
use App\Presentation\Components\Base\BaseComponent;
use App\Presentation\Control\Form\BaseForm;

class ComposeEmailForm extends BaseComponent
{
    public function __construct(
        private readonly MailQueueService $mailQueueService,
    ) {
    }


    public function createComponentForm(): BaseForm
    {
        $form = new BaseForm();

        $form->addText('recipients', 'Příjemci')
            ->setHtmlAttribute('placeholder', 'prvni@example.com, druhy@example.com')
            ->setRequired('Zadejte alespoň jednu e-mailovou adresu.')
            ->addRule($form::Pattern, 'Zadejte platné e-mailové adresy oddělené čárkou.', self::RECIPIENTS_PATTERN);

        $form->addText('subject', 'Předmět')
            ->setHtmlAttribute('placeholder', 'Předmět e-mailu')
            ->setRequired('Zadejte předmět.');

        $form->addTextArea('bodyHtml', 'Obsah (HTML)')
            ->setHtmlAttribute('rows', 12)
            ->setHtmlAttribute('placeholder', '<p>Text e-mailu...</p>')
            ->setRequired('Zadejte obsah e-mailu.');

        $form->addSubmit('submit', 'Odeslat e-mail');

        $form->onSuccess[] = fn(BaseForm $form, \stdClass $values) => $this->sendEmail($values);

        return $form;
    }


    private function sendEmail(\stdClass $values): void
    {
        $recipients = $this->parseRecipients($values->recipients);
        $mail = new ComposeMail($values->subject, $values->bodyHtml);

        foreach ($recipients as $recipient) {
            $this->mailQueueService->enqueue($recipient, $mail);
        }

        $this->flashSuccess(sprintf(
            'E-mail byl zařazen do fronty pro %d %s.',
            count($recipients),
            count($recipients) === 1 ? 'příjemce' : 'příjemců',
        ));

        $this->presenter->redirect(':Admin:Email:list');
    }


    /**
     * @return string[]
     */
    private function parseRecipients(string $input): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $input))));
    }


    // Regex: one or more comma-separated email addresses (with optional spaces)
    private const string RECIPIENTS_PATTERN =
        '[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}' .
        '(\s*,\s*[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})*';
}
