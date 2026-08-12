<?php declare(strict_types=1);
namespace App\Presentation\Modules\Admin\UserRole;

use App\Domain\UserRole\DefaultRoleException;
use App\Domain\UserRole\UserRoleFacade;
use App\Model\Security\Authorizator\InsufficientPrivilegesException;
use App\Presentation\Components\Admin\UserRole\UserRoleForm\UserRoleFormFactory;
use App\Presentation\Components\Admin\UserRole\UserRoleGrid\UserRoleGridFactory;
use App\Presentation\Components\Admin\UserRole\UserRolePermissionsForm\UserRolePermissionsFormFactory;
use App\Presentation\Modules\Admin\BaseAdminPresenter;
use App\Domain\UserRole\AclPermission;
use App\Presentation\Accessory\RequiresPermission;

#[RequiresPermission(AclPermission::RoleList)]
class UserRolePresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly UserRoleGridFactory $userRoleGridFactory,
        private readonly UserRoleFormFactory $userRoleFormFactory,
        private readonly UserRolePermissionsFormFactory $userRolePermissionsFormFactory,
        private readonly UserRoleFacade $userRoleFacade,
    ) {
        parent::__construct();
    }


    public function actionDefault(): void
    {
        $this->addComponent($this->userRoleGridFactory->create(), 'userRoleGrid');
    }


    #[RequiresPermission(AclPermission::RoleEdit)]
    public function actionCreate(): void
    {
        $this->addComponent($this->userRoleFormFactory->create(null), 'userRoleForm');
    }


    public function actionEdit(int $id): void
    {
        /** @var UserRoleEditTemplate $template */
        $template = $this->template;
        $template->userRole = $this->userRoleFacade->getRoleById($id);

        $this->addComponent($this->userRoleFormFactory->create($id), 'userRoleForm');
        $this->addComponent($this->userRolePermissionsFormFactory->create($id), 'userRolePermissionsForm');
    }


    #[RequiresPermission(AclPermission::RoleEdit)]
    public function handleDelete(int $id): void
    {
        try {
            $this->userRoleFacade->deleteRole($id);
            $this->flashSuccess('Role byla smazána.');
        } catch (DefaultRoleException $e) {
            $this->flashError($e->getMessage());
        } catch (InsufficientPrivilegesException) {
            $this->flashError(
                'Tuto roli nelze smazat — je poslední, která uděluje správu oprávnění. '
                . 'Nejdřív ji dejte jiné roli.',
            );
        }

        $this->redirect('default');
    }
}
