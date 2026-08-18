<?php declare(strict_types=1);

namespace App\Presentation\Modules\Admin\User;

use App\Domain\User\UserFacade;
use App\Presentation\Components\Admin\User\UserForm\UserFormFactory;
use App\Presentation\Components\Admin\User\UserListGrid\UserListGrid;
use App\Presentation\Components\Admin\User\UserListGrid\UserListGridFactory;
use App\Presentation\Modules\Admin\BaseAdminPresenter;
use App\Domain\User\UserPermission;
use App\Presentation\Accessory\RequiresPermission;

#[RequiresPermission(UserPermission::ListAll)]
class UserPresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly UserListGridFactory $userListGridFactory,
        private readonly UserFormFactory $userFormFactory,
        private readonly UserFacade $userFacade,
    ) {
        parent::__construct();
    }

    #[RequiresPermission(UserPermission::Create)]
    public function actionCreate(): void
    {
        $this->addComponent($this->userFormFactory->create(null), 'userForm');
    }

    #[RequiresPermission(UserPermission::Edit)]
    public function actionEdit(int $id): void
    {
        $this->addComponent($this->userFormFactory->create($id), 'userForm');
    }

    #[RequiresPermission(UserPermission::Edit)]
    public function handleSetActive(int $id, bool $active): void
    {
        $this->userFacade->setActive($id, $active);
        $this->flashSuccess($active ? 'Uživatel byl aktivován.' : 'Uživatel byl deaktivován.');
        $this->redirect('list');
    }

    public function createComponentUserListGrid(): UserListGrid
    {
        return $this->userListGridFactory->create();
    }
}
