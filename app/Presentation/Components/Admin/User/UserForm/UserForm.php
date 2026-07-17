<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\User\UserForm;

use App\Domain\User\UserFacade;
use App\Domain\User\UserFormData;
use App\Domain\UserRole\UserRole;
use App\Presentation\Components\Base\BaseComponent;
use App\Presentation\Control\Form\BaseForm;
use Nette\Forms\Control;

class UserForm extends BaseComponent
{
    public function __construct(
        private readonly UserFacade $userFacade,
        private readonly ?int $editId,
    ) {
    }


    public function createComponentForm(): BaseForm
    {
        $form = new BaseForm();

        $form->addEmail(UserFormData::PARAM_EMAIL, 'E-mail')
            ->setRequired('Zadejte e-mail.')
            ->addRule(
                fn(Control $control): bool => !$this->userFacade->emailExists((string) $control->getValue(), $this->editId),
                'Uživatel s tímto e-mailem již existuje.',
            );

        $passwordLabel = $this->editId !== null ? 'Nové heslo' : 'Heslo';
        $password = $form->addPassword(UserFormData::PARAM_PASSWORD, $passwordLabel);
        $password->addCondition($form::Filled)
            ->addRule($form::MinLength, 'Heslo musí mít alespoň 8 znaků.', 8)
            ->endCondition();

        if ($this->editId !== null) {
            $password->setOption('description', 'Nechte prázdné pro zachování stávajícího hesla.');
        } else {
            $password->setRequired('Zadejte heslo.');
        }

        $form->addSelect(UserFormData::PARAM_ROLE, 'Role', [
            UserRole::User->value       => UserRole::User->toLabel(),
            UserRole::Admin->value      => UserRole::Admin->toLabel(),
            UserRole::SuperAdmin->value => UserRole::SuperAdmin->toLabel(),
        ])->setRequired();

        $form->addCheckbox(UserFormData::PARAM_ACTIVE, 'Aktivní');

        $form->addSubmit('submit', $this->editId !== null ? 'Uložit změny' : 'Vytvořit uživatele');

        if ($this->editId !== null) {
            $user = $this->userFacade->getUserById($this->editId);
            $form->setDefaults([
                UserFormData::PARAM_EMAIL  => $user->email,
                UserFormData::PARAM_ROLE   => $user->role->value,
                UserFormData::PARAM_ACTIVE => $user->active,
            ]);
        } else {
            $form->setDefaults([UserFormData::PARAM_ACTIVE => true]);
        }

        $form->onSuccess[] = fn(BaseForm $form, UserFormData $data) => $this->saveForm($data);

        return $form;
    }


    public function render(mixed $params = null): void
    {
        $this->getTemplate()->editId = $this->editId;
        $this->getTemplate()->backLink = $this->presenter->link('User:list');
        parent::render($params);
    }


    private function saveForm(UserFormData $data): void
    {
        if ($this->editId !== null) {
            $this->userFacade->update($this->editId, $data);
            $this->flashSuccess('Uživatel byl uložen.');
            $this->presenter->redirect('edit', ['id' => $this->editId]);
        } else {
            $user = $this->userFacade->create($data);
            $this->flashSuccess('Uživatel byl vytvořen.');
            $this->presenter->redirect('edit', ['id' => $user->id]);
        }
    }
}
