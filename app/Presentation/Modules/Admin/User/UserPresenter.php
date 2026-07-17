<?php declare(strict_types=1);

namespace App\Presentation\Modules\Admin\User;

use App\Presentation\Components\Admin\User\UserListGrid\UserListGrid;
use App\Presentation\Components\Admin\User\UserListGrid\UserListGridFactory;
use App\Presentation\Modules\Admin\BaseAdminPresenter;

class UserPresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly UserListGridFactory $userListGridFactory,
    ) {
        parent::__construct();
    }

    public function createComponentUserListGrid(): UserListGrid
    {
        return $this->userListGridFactory->create();
    }
}
