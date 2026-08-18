<?php declare(strict_types=1);

namespace App\Presentation\Modules\Admin\AppSettings;

use App\Presentation\Components\Admin\AppSettings\AppSettingsGeneralForm\AppSettingsGeneralFormFactory;
use App\Presentation\Modules\Admin\BaseAdminPresenter;
use App\Domain\AppSettings\AppSettingsPermission;
use App\Presentation\Accessory\RequiresPermission;

#[RequiresPermission(AppSettingsPermission::View)]
class AppSettingsPresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly AppSettingsGeneralFormFactory $generalFormFactory,
    ) {
        parent::__construct();
    }


    public function actionGeneral(): void
    {
        $this->addComponent($this->generalFormFactory->create(), 'generalForm');
    }
}
