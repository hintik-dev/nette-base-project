<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\Sign\PasswordResetRequestForm;

use App\Domain\PasswordReset\PasswordResetFacade;
use App\Domain\PasswordReset\PasswordResetRequestFormData;
use App\Presentation\Components\Base\BaseComponent;
use App\Presentation\Control\Form\BaseForm;

class PasswordResetRequestForm extends BaseComponent
{
    public function __construct(
        private readonly PasswordResetFacade $facade,
    ) {
    }


    public function createComponentForm(): BaseForm
    {
        $form = new BaseForm();

        $form->addEmail(PasswordResetRequestFormData::PARAM_EMAIL, 'E-mail')
            ->setRequired('Zadejte e-mail.');

        // Místo pro budoucí reCAPTCHA widget (fáze 2) — ověření by šlo do saveForm() před requestReset().

        $form->addSubmit('submit', 'Odeslat odkaz pro obnovu hesla');

        $form->onSuccess[] = fn(BaseForm $form, PasswordResetRequestFormData $data) => $this->saveForm($data);

        return $form;
    }


    private function saveForm(PasswordResetRequestFormData $data): void
    {
        $this->facade->requestReset($data->email);

        // Vždy stejná zpráva bez ohledu na to, jestli e-mail v systému existuje (ochrana proti enumeraci účtů).
        $this->flashSuccess('Pokud účet s tímto e-mailem existuje, poslali jsme na něj odkaz pro obnovu hesla.');
        $this->presenter->redirect(':Admin:Sign:in');
    }
}
