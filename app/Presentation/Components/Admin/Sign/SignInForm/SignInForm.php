<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\Sign\SignInForm;

use App\Domain\Sign\SignFacade;
use App\Domain\Sign\SignInFormData;
use App\Presentation\Components\Base\BaseComponent;
use App\Presentation\Control\Form\BaseForm;
use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator;
use Throwable;

class SignInForm extends BaseComponent
{
    /**
     * @param SignFacade $signFacade
     * @param array<callable(): void> $onLogIn
     */
    public function __construct(
        private readonly SignFacade $signFacade,
        private readonly array $onLogIn,
    ) {
    }


    public function createComponentForm(): BaseForm
    {
        $form = new BaseForm();

        $form->addText(SignInFormData::PARAM_LOGIN, 'Přihlašovací jméno')
            ->setRequired();

        $form->addPassword(SignInFormData::PARAM_PASSWORD, 'Heslo')
            ->setRequired();

        $form->addSubmit('submit', 'Přihlásit se');

        $form->onSuccess[] = fn(BaseForm $form, SignInFormData $values) => $this->saveForm($form, $values);
        return $form;
    }

    private function saveForm(BaseForm $form, SignInFormData $formData): void
    {
        try {
            $this->signFacade->signUserIn($formData);

            $this->presenter->flashMessage('Uživatel úspěšně přihlášen');

            foreach ($this->onLogIn as $callback) {
                $callback();
            }
        } catch (AuthenticationException $e) {
            if ($e->getCode() === Authenticator::NotApproved) {
                $this->presenter->flashMessage('Uživatelský účet je blokován, není možné přihlášení', 'error');
                $form->addError('Uživatelský účet je blokován, není možné přihlášení');
                return;
            }

            $this->presenter->flashMessage('Zadaná kombinace údajů neodpovídá žádnému uživateli', 'error');
            $form->addError('Zadaná kombinace údajů neodpovídá žádnému uživateli');
        } catch (Throwable) {
            $this->presenter->flashMessage('Při přihlášení se vyskytla chyba', 'error');
            $form->addError('Při přihlášení se vyskytla chyba');
        }
    }
}
