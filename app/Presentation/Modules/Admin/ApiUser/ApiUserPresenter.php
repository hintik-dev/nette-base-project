<?php declare(strict_types=1);
namespace App\Presentation\Modules\Admin\ApiUser;

use App\Domain\ApiUser\ApiUserFacade;
use App\Presentation\Components\Admin\ApiUser\ApiUserForm\ApiUserFormFactory;
use App\Presentation\Components\Admin\ApiUser\ApiUserGrid\ApiUserGridFactory;
use App\Presentation\Modules\Admin\BaseAdminPresenter;

class ApiUserPresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly ApiUserGridFactory $apiUserGridFactory,
        private readonly ApiUserFormFactory $apiUserFormFactory,
        private readonly ApiUserFacade $apiUserFacade,
    ) {
        parent::__construct();
    }


    public function actionDefault(): void
    {
        $this->addComponent($this->apiUserGridFactory->create(), 'apiUserGrid');
    }


    public function actionCreate(): void
    {
        $this->addComponent($this->apiUserFormFactory->create(null), 'apiUserForm');
    }


    public function actionEdit(int $id): void
    {
        $this->template->apiUser = $this->apiUserFacade->getById($id);
        $this->addComponent($this->apiUserFormFactory->create($id), 'apiUserForm');
    }


    public function handleRegenerateToken(int $id): void
    {
        $this->apiUserFacade->regenerateToken($id);
        $this->flashSuccess('API token byl přegenerován.');
        $this->redirect('edit', ['id' => $id]);
    }


    public function handleDelete(int $id): void
    {
        $this->apiUserFacade->delete($id);
        $this->flashSuccess('API uživatel byl smazán.');
        $this->redirect('default');
    }
}
