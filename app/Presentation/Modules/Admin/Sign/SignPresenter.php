<?php declare(strict_types=1);
namespace App\Presentation\Modules\Admin\Sign;

use App\Domain\PasswordReset\PasswordResetFacade;
use App\Presentation\Components\Admin\Sign\PasswordResetForm\PasswordResetForm;
use App\Presentation\Components\Admin\Sign\PasswordResetForm\PasswordResetFormFactory;
use App\Presentation\Components\Admin\Sign\PasswordResetRequestForm\PasswordResetRequestForm;
use App\Presentation\Components\Admin\Sign\PasswordResetRequestForm\PasswordResetRequestFormFactory;
use App\Presentation\Components\Admin\Sign\SignInForm\SignInForm;
use App\Presentation\Components\Admin\Sign\SignInForm\SignInFormFactory;
use App\Presentation\Modules\Admin\BaseAdminPresenter;
use Override;
use ReflectionClass;
use ReflectionMethod;

class SignPresenter extends BaseAdminPresenter
{
    /** Akce dostupné bez ohledu na to, jestli je uživatel už přihlášený. */
    private const array ACTIONS_WITHOUT_LOGIN_REDIRECT = ['out', 'forgotPassword', 'resetPassword'];

    private ?string $resetToken = null;

    public function __construct(
        private readonly SignInFormFactory $signInFormFactory,
        private readonly PasswordResetRequestFormFactory $passwordResetRequestFormFactory,
        private readonly PasswordResetFormFactory $passwordResetFormFactory,
        private readonly PasswordResetFacade $passwordResetFacade,
    ) {
        parent::__construct();
    }

    public function actionOut(): void
    {
        $this->user->logout();
        $this->getHttpResponse()->deleteCookie('admin_theme');
        $this->redirect(':Web:Page:default', ['slug' => '']);
    }


    public function actionForgotPassword(): void
    {
    }


    public function actionResetPassword(?string $token = null): void
    {
        if ($token === null || !$this->passwordResetFacade->isTokenValid($token)) {
            $this->flashError('Odkaz pro obnovu hesla je neplatný nebo vypršel.');
            $this->redirect('forgotPassword');
        }

        $this->resetToken = $token;
    }


    public function createComponentSignInForm(): SignInForm
    {
        return $this->signInFormFactory->create([function () {
            $this->redirect(':Admin:Home:');
        }]);
    }


    public function createComponentPasswordResetRequestForm(): PasswordResetRequestForm
    {
        return $this->passwordResetRequestFormFactory->create();
    }


    public function createComponentPasswordResetForm(): PasswordResetForm
    {
        return $this->passwordResetFormFactory->create($this->resetToken ?? '');
    }


    /**
     * @param ReflectionMethod|ReflectionClass $element
     * @return void
     * @phpstan-ignore-next-line
     */
    #[Override]
    public function checkRequirements(ReflectionMethod|ReflectionClass $element): void
    {
        if (in_array($this->action, self::ACTIONS_WITHOUT_LOGIN_REDIRECT, true) && $this->user->isLoggedIn()) {
            return;
        }

        if ($this->user->isLoggedIn()) {
            $this->redirect(':Admin:Home:');
        }
    }
}
