<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\UserSettings\UserSettingsForm;

use App\Domain\UserSettings\AppearanceTheme;
use App\Domain\UserSettings\UserSettingsFacade;
use App\Domain\UserSettings\UserSettingsFormData;
use App\Presentation\Components\Base\BaseComponent;
use App\Presentation\Control\Form\BaseForm;

class UserSettingsForm extends BaseComponent
{
    public function __construct(
        private readonly UserSettingsFacade $userSettingsFacade,
    ) {
    }


    public function createComponentForm(): BaseForm
    {
        $form = new BaseForm();

        $form->addRadioList(UserSettingsFormData::PARAM_THEME, 'Motiv vzhledu', [
            AppearanceTheme::Light->value  => AppearanceTheme::Light->getLabel(),
            AppearanceTheme::Dark->value   => AppearanceTheme::Dark->getLabel(),
            AppearanceTheme::System->value => AppearanceTheme::System->getLabel(),
        ])->setRequired();

        $form->addSubmit('submit', 'Uložit nastavení');

        $settings = $this->userSettingsFacade->getCurrentUserSettings();
        $form->setDefaults([
            UserSettingsFormData::PARAM_THEME => $settings->theme->value,
        ]);

        $form->onSuccess[] = fn(BaseForm $form, UserSettingsFormData $data) => $this->saveForm($data);

        return $form;
    }


    private function saveForm(UserSettingsFormData $data): void
    {
        $theme = AppearanceTheme::from($data->theme);

        $this->userSettingsFacade->saveCurrentUserTheme($theme);

        $this->presenter->getHttpResponse()->setCookie('admin_theme', $theme->value, '+1 year');

        $this->flashSuccess('Nastavení bylo uloženo');
        $this->presenter->redirect('this');
    }
}
