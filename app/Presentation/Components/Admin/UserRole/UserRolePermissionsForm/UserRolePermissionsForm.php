<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\UserRole\UserRolePermissionsForm;

use App\Domain\UserRole\PermissionEffect;
use App\Domain\UserRole\UserRoleFacade;
use App\Model\Security\Authorizator\InsufficientPrivilegesException;
use App\Presentation\Components\Base\BaseComponent;
use App\Presentation\Control\Form\BaseForm;

/**
 * Třístavová matice oprávnění jedné role.
 *
 * Neutrální stav se v databázi neukládá, je to absence záznamu — ve formuláři
 * ale musí být plnohodnotná volba, aby šlo dřív nastavené allow/deny vzít zpět.
 *
 * @property-read UserRolePermissionsFormTemplate $template
 */
class UserRolePermissionsForm extends BaseComponent
{
    public const string EFFECT_NEUTRAL = 'neutral';

    public function __construct(
        private readonly UserRoleFacade $userRoleFacade,
        private readonly int $roleId,
    ) {
    }


    public function createComponentForm(): BaseForm
    {
        $form = new BaseForm();
        $current = $this->userRoleFacade->getPermissionsForRole($this->roleId);

        $options = [
            PermissionEffect::Allow->value => PermissionEffect::Allow->getLabel(),
            self::EFFECT_NEUTRAL => 'Neutrální',
            PermissionEffect::Deny->value => PermissionEffect::Deny->getLabel(),
        ];

        $controlNames = $this->getControlNames();

        foreach ($this->userRoleFacade->getPermissionRegistry()->getAll() as $key => $definition) {
            $form->addRadioList($controlNames[$key], $definition->getLabel(), $options)
                ->setDefaultValue(isset($current[$key]) ? $current[$key]->value : self::EFFECT_NEUTRAL)
                ->setHtmlAttribute('class', 'btn-check');
        }

        $form->addSubmit('submit', 'Uložit oprávnění');

        $form->onSuccess[] = fn (BaseForm $form) => $this->saveForm($form);

        return $form;
    }


    public function render(mixed $params = null): void
    {
        $role = $this->userRoleFacade->getRoleById($this->roleId);

        $this->template->groups = $this->buildGroups();
        $this->template->orphanKeys = $this->userRoleFacade->getOrphanPermissionKeys();
        $this->template->roleName = $role->name;
        $this->template->isDefaultRole = $role->isDefault();

        parent::render($params);
    }


    /**
     * @return array<string, list<PermissionMatrixRow>>
     */
    private function buildGroups(): array
    {
        $controlNames = $this->getControlNames();
        $groups = [];

        foreach ($this->userRoleFacade->getPermissionRegistry()->getGrouped() as $group => $definitions) {
            foreach ($definitions as $definition) {
                $groups[$group][] = new PermissionMatrixRow(
                    key: $definition->getKey(),
                    label: $definition->getLabel(),
                    controlName: $controlNames[$definition->getKey()],
                    scope: $definition->getScope(),
                );
            }
        }

        return $groups;
    }


    /** @return array<string, string> klíč oprávnění => název prvku */
    private function getControlNames(): array
    {
        return PermissionControlName::buildMap(
            array_keys($this->userRoleFacade->getPermissionRegistry()->getAll()),
        );
    }


    private function saveForm(BaseForm $form): void
    {
        /** @var array<string, string> $values */
        $values = (array) $form->getValues('array');

        $permissions = [];

        foreach ($this->getControlNames() as $key => $controlName) {
            $effect = PermissionEffect::tryFrom($values[$controlName] ?? '');

            if ($effect !== null) {
                $permissions[$key] = $effect;
            }
        }

        try {
            $this->userRoleFacade->setPermissionsForRole($this->roleId, $permissions);
        } catch (InsufficientPrivilegesException) {
            $form->addError(
                'Tímto zásahem by správu oprávnění neměl kdo dělat — je to poslední role, '
                . 'která ji uděluje. Nejdřív ji dejte jiné roli.',
            );
            return;
        }

        $this->flashSuccess('Oprávnění role byla uložena.');
        $this->presenter->redirect('edit', ['id' => $this->roleId]);
    }
}
