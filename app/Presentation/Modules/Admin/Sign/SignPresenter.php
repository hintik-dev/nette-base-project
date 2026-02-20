<?php

namespace App\Presentation\Modules\Admin\Sign;

use App\Presentation\Components\Admin\Sign\SignInForm\SignInForm;
use App\Presentation\Components\Admin\Sign\SignInForm\SignInFormFactory;
use App\Presentation\Modules\Admin\BaseAdminPresenter;
use Override;
use ReflectionClass;
use ReflectionMethod;

class SignPresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly SignInFormFactory $signInFormFactory,
    )
    {
        parent::__construct();
    }

    public function actionOut(): void
    {
        $this->user->logout();
        $this->redirect(':Web:Home:');
    }

    public function createComponentSignInForm(): SignInForm
    {
        return $this->signInFormFactory->create([function ()
        {
            $this->redirect(':Admin:Home:');
        }]);
    }


    /**
     * @param ReflectionMethod|ReflectionClass $element
     * @return void
     * @phpstan-ignore-next-line
     */
    #[Override]
    public function checkRequirements(ReflectionMethod|ReflectionClass $element): void
    {
        if ($this->action === 'out' && $this->user->isLoggedIn())
        {
            return;
        }

        if ($this->user->isLoggedIn())
        {
            $this->redirect('Admin:Home:');
        }
    }


}
