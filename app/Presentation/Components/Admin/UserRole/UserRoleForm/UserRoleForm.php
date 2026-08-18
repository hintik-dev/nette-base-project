<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\UserRole\UserRoleForm;

use App\Domain\UserRole\DefaultRoleException;
use App\Domain\UserRole\UserRole;
use App\Domain\UserRole\UserRoleConflictException;
use App\Domain\UserRole\UserRoleFacade;
use App\Domain\UserRole\UserRoleFormData;
use App\Presentation\Components\Base\BaseComponent;
use App\Presentation\Control\Form\BaseForm;
use Nette\Forms\Control;

/**
 * @property-read UserRoleFormTemplate $template
 */
class UserRoleForm extends BaseComponent
{
    public function __construct(
        private readonly UserRoleFacade $userRoleFacade,
        private readonly ?int $editId,
    ) {
    }


    public function createComponentForm(): BaseForm
    {
        $form = new BaseForm();
        $role = $this->editId !== null ? $this->userRoleFacade->getRoleById($this->editId) : null;
        $isDefault = $role !== null && $role->isDefault();

        $form->addText(UserRoleFormData::PARAM_NAME, 'Název')
            ->setRequired('Zadejte název role.')
            ->setMaxLength(255);

        $code = $form->addText(UserRoleFormData::PARAM_CODE, 'Kód')
            ->setRequired('Zadejte kód role.')
            ->setMaxLength(64)
            ->addRule($form::Pattern, 'Kód smí obsahovat jen malá písmena, číslice a pomlčky.', '[a-z0-9-]+');

        if ($role !== null) {
            // Kód je strojový identifikátor, na který se může odkazovat kód
            // i migrace — u existující role se proto nemění.
            $code->setDisabled();
        } else {
            $code->addRule(
                fn (Control $control): bool => !$this->userRoleFacade->codeExists((string) $control->getValue()),
                'Role s tímto kódem už existuje.',
            );
        }

        $form->addTextArea(UserRoleFormData::PARAM_DESCRIPTION, 'Popis')
            ->setMaxLength(255);

        $priority = $form->addInteger(UserRoleFormData::PARAM_PRIORITY, 'Priorita')
            ->setRequired('Zadejte prioritu role.');

        if ($isDefault) {
            $priority->setDisabled();
        } else {
            $priority
                ->addRule($form::Min, 'Priorita běžné role musí být větší než %d.', UserRole::DEFAULT_PRIORITY + 1)
                ->addRule(
                    fn (Control $control): bool => !$this->userRoleFacade->priorityExists((int) $control->getValue(), $this->editId),
                    'Tuto prioritu už používá jiná role. Priority musí být unikátní, aby při skládání oprávnění nevznikla nejednoznačnost.',
                );
        }

        $form->addSubmit('submit', $this->editId !== null ? 'Uložit změny' : 'Vytvořit roli');

        if ($role !== null) {
            $form->setDefaults([
                UserRoleFormData::PARAM_NAME => $role->name,
                UserRoleFormData::PARAM_CODE => $role->code,
                UserRoleFormData::PARAM_DESCRIPTION => $role->description ?? '',
                UserRoleFormData::PARAM_PRIORITY => $role->priority,
            ]);
        }

        $form->onSuccess[] = fn (BaseForm $form, UserRoleFormData $data) => $this->saveForm($form, $data);

        return $form;
    }


    public function render(mixed $params = null): void
    {
        $this->template->editId = $this->editId;
        $this->template->isDefaultRole = $this->editId !== null
            && $this->userRoleFacade->getRoleById($this->editId)->isDefault();
        parent::render($params);
    }


    private function saveForm(BaseForm $form, UserRoleFormData $data): void
    {
        try {
            if ($this->editId !== null) {
                // Vypnutá pole se z formuláře nevrací; u výchozí role je proto
                // nutné prioritu doplnit, jinak by se přepsala nulou.
                $role = $this->userRoleFacade->getRoleById($this->editId);
                $data->code = $role->code;

                if ($role->isDefault()) {
                    $data->priority = $role->priority;
                }

                $this->userRoleFacade->updateRole($this->editId, $data);
                $this->flashSuccess('Role byla uložena.');
                $this->presenter->redirect('edit', ['id' => $this->editId]);
            } else {
                $role = $this->userRoleFacade->createRole($data);
                $this->flashSuccess('Role byla vytvořena. Teď jí nastavte oprávnění.');
                $this->presenter->redirect('edit', ['id' => $role->id]);
            }
        } catch (UserRoleConflictException | DefaultRoleException $e) {
            $form->addError($e->getMessage());
        }
    }
}
