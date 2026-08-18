<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\User\UserForm;

use App\Domain\User\UserFacade;
use App\Domain\User\UserFormData;
use App\Domain\UserRole\UserRoleFacade;
use App\Presentation\Components\Base\BaseComponent;
use App\Presentation\Control\Form\BaseForm;
use Nette\Forms\Control;

/**
 * @property-read UserFormTemplate $template
 */
class UserForm extends BaseComponent
{
    public function __construct(
        private readonly UserFacade $userFacade,
        private readonly UserRoleFacade $userRoleFacade,
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

        // Role se nabízejí jen tomu, kdo je smí přiřazovat. Výchozí role mezi
        // nimi není — aplikuje se vždy, i uživateli bez jakékoli role.
        $canAssignRoles = $this->userFacade->canAssignRoles();

        if ($canAssignRoles) {
            $form->addMultiSelect(UserFormData::PARAM_ROLE_IDS, 'Role', $this->getRoleOptions())
                ->setOption('description', 'Bez vybrané role platí uživateli jen výchozí role.');
        }

        $form->addCheckbox(UserFormData::PARAM_ACTIVE, 'Aktivní');

        $form->addSubmit('submit', $this->editId !== null ? 'Uložit změny' : 'Vytvořit uživatele');

        if ($this->editId !== null) {
            $user = $this->userFacade->getUserById($this->editId);
            $defaults = [
                UserFormData::PARAM_EMAIL  => $user->email,
                UserFormData::PARAM_ACTIVE => $user->active,
            ];

            if ($canAssignRoles) {
                $defaults[UserFormData::PARAM_ROLE_IDS] = $this->userRoleFacade->getRoleIdsForUser($this->editId);
            }

            $form->setDefaults($defaults);
        } else {
            $form->setDefaults([UserFormData::PARAM_ACTIVE => true]);
        }

        $form->onSuccess[] = fn(BaseForm $form, UserFormData $data) => $this->saveForm($data);

        return $form;
    }


    /** @return array<int, string> */
    private function getRoleOptions(): array
    {
        $options = [];

        foreach ($this->userRoleFacade->getAssignableRoles() as $role) {
            $options[$role->id] = $role->name;
        }

        return $options;
    }


    public function render(mixed $params = null): void
    {
        $this->template->editId = $this->editId;
        $this->template->backLink = $this->presenter->link('User:list');
        $this->template->canAssignRoles = $this->userFacade->canAssignRoles();
        $this->template->isSuperadmin = $this->editId !== null
            && $this->userFacade->getUserById($this->editId)->isSuperadmin;
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
