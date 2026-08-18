<?php declare(strict_types=1);
namespace App\Presentation\Modules\Admin\ApiUser;

use App\Domain\ApiUser\ApiUserFacade;
use App\Presentation\Components\Admin\ApiUser\ApiUserForm\ApiUserFormFactory;
use App\Presentation\Components\Admin\ApiUser\ApiUserGrid\ApiUserGridFactory;
use App\Presentation\Modules\Admin\BaseAdminPresenter;
use App\Domain\ApiUser\ApiUserPermission;
use App\Presentation\Accessory\RequiresPermission;

#[RequiresPermission(ApiUserPermission::ListAll)]
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


    #[RequiresPermission(ApiUserPermission::Create)]
    public function actionCreate(): void
    {
        $this->addComponent($this->apiUserFormFactory->create(null), 'apiUserForm');
    }


    #[RequiresPermission(ApiUserPermission::Edit)]
    public function actionEdit(int $id): void
    {
        /** @var ApiUserEditTemplate $template */
        $template = $this->template;
        $template->apiUser = $this->apiUserFacade->getById($id);
        $this->addComponent($this->apiUserFormFactory->create($id), 'apiUserForm');
    }


    #[RequiresPermission(ApiUserPermission::RegenerateToken)]
    public function handleRegenerateToken(int $id): void
    {
        $this->apiUserFacade->regenerateToken($id);
        $this->flashSuccess('API token byl přegenerován.');
        $this->redirect('edit', ['id' => $id]);
    }


    #[RequiresPermission(ApiUserPermission::Delete)]
    public function handleDelete(int $id): void
    {
        $this->apiUserFacade->delete($id);
        $this->flashSuccess('API uživatel byl smazán.');
        $this->redirect('default');
    }
}
