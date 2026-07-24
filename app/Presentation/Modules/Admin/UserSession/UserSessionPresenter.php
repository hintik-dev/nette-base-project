<?php declare(strict_types=1);
namespace App\Presentation\Modules\Admin\UserSession;

use App\Presentation\Components\Admin\UserSession\UserSessionGrid\UserSessionGridFactory;
use App\Presentation\Modules\Admin\BaseAdminPresenter;

class UserSessionPresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly UserSessionGridFactory $userSessionGridFactory,
    ) {
        parent::__construct();
    }


    public function actionDefault(): void
    {
        $this->addComponent($this->userSessionGridFactory->create(), 'userSessionGrid');
    }
}
