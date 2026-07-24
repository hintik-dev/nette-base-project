<?php declare(strict_types=1);
namespace App\Presentation\Modules\Admin;

use App\Domain\UserSettings\AppearanceTheme;
use App\Domain\UserSettings\UserSettingsFacade;
use App\Presentation\Modules\Base\BasePresenter;
use Override;
use ReflectionClass;
use ReflectionMethod;

class BaseAdminPresenter extends BasePresenter
{
    private const string THEME_COOKIE = 'admin_theme';

    private UserSettingsFacade $userSettingsFacade;

    private ?AppearanceTheme $adminTheme = null;

    public function injectUserSettingsFacade(UserSettingsFacade $userSettingsFacade): void
    {
        $this->userSettingsFacade = $userSettingsFacade;
    }


    /**
     * @param ReflectionMethod|ReflectionClass $element
     * @return void
     * @phpstan-ignore-next-line
     */
    #[Override]
    public function checkRequirements(ReflectionMethod|ReflectionClass $element): void
    {
        if (!$this->user->isLoggedIn()) {
            $this->redirect(
                destination: ':Admin:Sign:in',
                args: ['key' => $this->storeRequest()]
            );
        }

        parent::checkRequirements($element);
    }


    protected function startup(): void
    {
        parent::startup();

        if ($this->user->isLoggedIn() && $this->getHttpRequest()->getCookie(self::THEME_COOKIE) === null) {
            $settings = $this->userSettingsFacade->getCurrentUserSettings();
            $this->adminTheme = $settings->theme;
            $this->getHttpResponse()->setCookie(self::THEME_COOKIE, $this->adminTheme->value, '+1 year');
        }
    }


    public function getAdminTheme(): AppearanceTheme
    {
        if ($this->adminTheme !== null) {
            return $this->adminTheme;
        }

        $cookie = $this->getHttpRequest()->getCookie(self::THEME_COOKIE);
        return $cookie !== null ? AppearanceTheme::from($cookie) : AppearanceTheme::Light;
    }
}
