<?php declare(strict_types=1);
namespace App\Presentation\Modules\Admin\UserSettings;

use App\Presentation\Components\Admin\UserSettings\UserSettingsForm\UserSettingsFormFactory;
use App\Presentation\Modules\Admin\BaseAdminPresenter;

class UserSettingsPresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly UserSettingsFormFactory $userSettingsFormFactory,
    ) {
        parent::__construct();
    }


    public function actionDefault(): void
    {
        $this->addComponent($this->userSettingsFormFactory->create(), 'userSettingsForm');
    }
}
