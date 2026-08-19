<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\Sign\PasswordResetForm;

use App\Domain\PasswordReset\PasswordResetFacade;
use App\Domain\PasswordReset\PasswordResetFormData;
use App\Domain\PasswordReset\PasswordResetTokenInvalidException;
use App\Presentation\Components\Base\BaseComponent;
use App\Presentation\Control\Form\BaseForm;

class PasswordResetForm extends BaseComponent
{
    public function __construct(
        private readonly PasswordResetFacade $facade,
        private readonly string $token,
    ) {
    }


    public function createComponentForm(): BaseForm
    {
        $form = new BaseForm();

        $password = $form->addPassword(PasswordResetFormData::PARAM_PASSWORD, 'Nové heslo')
            ->setRequired('Zadejte nové heslo.')
            ->addRule($form::MinLength, 'Heslo musí mít alespoň 8 znaků.', 8);

        $form->addPassword(PasswordResetFormData::PARAM_PASSWORD_CONFIRM, 'Potvrzení hesla')
            ->setRequired('Potvrďte nové heslo.')
            ->addRule($form::Equal, 'Hesla se neshodují.', $password);

        $form->addSubmit('submit', 'Nastavit nové heslo');

        $form->onSuccess[] = fn(BaseForm $form, PasswordResetFormData $data) => $this->saveForm($form, $data);

        return $form;
    }


    private function saveForm(BaseForm $form, PasswordResetFormData $data): void
    {
        try {
            $this->facade->resetPassword($this->token, $data->password);
        } catch (PasswordResetTokenInvalidException $e) {
            $form->addError($e->getMessage());
            return;
        }

        $this->flashSuccess('Heslo bylo úspěšně změněno. Nyní se můžete přihlásit.');
        $this->presenter->redirect(':Admin:Sign:in');
    }
}
