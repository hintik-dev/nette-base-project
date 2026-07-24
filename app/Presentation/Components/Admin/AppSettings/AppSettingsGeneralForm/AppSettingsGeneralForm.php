<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\AppSettings\AppSettingsGeneralForm;

use App\Domain\AppSettings\AppSettingsFacade;
use App\Domain\AppSettings\AppSettingsFormData;
use App\Presentation\Components\Base\BaseComponent;
use App\Presentation\Control\Form\BaseForm;

class AppSettingsGeneralForm extends BaseComponent
{
    public function __construct(
        private readonly AppSettingsFacade $facade,
    ) {
    }


    public function createComponentForm(): BaseForm
    {
        $form = new BaseForm();

        $form->addText('siteName', 'Název aplikace')
            ->setHtmlAttribute('placeholder', 'Moje aplikace')
            ->setNullable();

        $form->addText('contactEmail', 'Kontaktní e-mail')
            ->setHtmlAttribute('placeholder', 'kontakt@example.com')
            ->setNullable()
            ->addCondition($form::Filled)
                ->addRule($form::Email, 'Zadejte platnou e-mailovou adresu.')
            ->endCondition();

        $form->addSubmit('submit', 'Uložit');

        $form->setDefaults([
            'siteName'     => $this->facade->getSiteName(),
            'contactEmail' => $this->facade->getContactEmail(),
        ]);

        $form->onSuccess[] = fn(BaseForm $form, AppSettingsFormData $data) => $this->saveForm($data);

        return $form;
    }


    private function saveForm(AppSettingsFormData $data): void
    {
        $this->facade->saveGeneralSettings($data->siteName ?? '', $data->contactEmail ?? '');
        $this->flashSuccess('Nastavení bylo uloženo.');
        $this->presenter->redirect('this');
    }
}
