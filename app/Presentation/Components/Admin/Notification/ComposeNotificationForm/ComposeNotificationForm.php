<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\Notification\ComposeNotificationForm;

use App\Domain\Notification\NotificationService;
use App\Domain\Notification\NotificationType;
use App\Presentation\Components\Base\BaseComponent;
use App\Presentation\Control\Form\BaseForm;
use stdClass;

class ComposeNotificationForm extends BaseComponent
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {
    }


    public function createComponentForm(): BaseForm
    {
        $form = new BaseForm();

        $form->addSelect('type', 'Typ', $this->getTypeOptions())
            ->setRequired()
            ->setDefaultValue(NotificationType::System->value);

        $form->addText('title', 'Nadpis')
            ->setRequired('Zadejte nadpis.');

        $form->addTextArea('message', 'Zpráva')
            ->setHtmlAttribute('rows', 6)
            ->setRequired('Zadejte text zprávy.');

        $form->addSubmit('submit', 'Odeslat všem aktivním uživatelům');

        $form->onSuccess[] = fn(BaseForm $form, stdClass $values) => $this->saveForm($values);

        return $form;
    }


    private function saveForm(stdClass $values): void
    {
        $this->notificationService->broadcastToActive(
            NotificationType::from($values->type),
            $values->title,
            $values->message,
        );

        $this->flashSuccess('Notifikace byla odeslána všem aktivním uživatelům.');
        $this->presenter->redirect(':Admin:Notification:default');
    }


    /** @return array<string, string> */
    private function getTypeOptions(): array
    {
        return [
            NotificationType::System->value   => 'Systém',
            NotificationType::Info->value     => 'Info',
            NotificationType::Success->value  => 'Úspěch',
            NotificationType::Warning->value  => 'Upozornění',
            NotificationType::Security->value => 'Zabezpečení',
        ];
    }
}
