<?php declare(strict_types=1);
namespace App\Presentation\Modules\Admin\Notification;

use App\Domain\Notification\NotificationFacade;
use App\Domain\Notification\NotificationPermission;
use App\Presentation\Accessory\RequiresPermission;
use App\Presentation\Components\Admin\Notification\ComposeNotificationForm\ComposeNotificationFormFactory;
use App\Presentation\Components\Admin\Notification\NotificationGrid\NotificationGridFactory;
use App\Presentation\Modules\Admin\BaseAdminPresenter;

class NotificationPresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly NotificationGridFactory $gridFactory,
        private readonly ComposeNotificationFormFactory $composeFormFactory,
        private readonly NotificationFacade $facade,
    ) {
        parent::__construct();
    }


    public function actionDefault(): void
    {
        $this->addComponent($this->gridFactory->create(), 'notificationGrid');
    }


    #[RequiresPermission(NotificationPermission::Send)]
    public function actionCompose(): void
    {
        $this->addComponent($this->composeFormFactory->create(), 'composeNotificationForm');
    }


    public function handleMarkAsRead(int $id): void
    {
        $this->facade->markAsRead($id);
        $this->redirect('this');
    }


    public function handleHide(int $id): void
    {
        $this->facade->hide($id);
        $this->redirect('this');
    }
}
