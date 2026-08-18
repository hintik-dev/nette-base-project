<?php declare(strict_types=1);
namespace App\Presentation\Modules\Admin;

use App\Domain\UserSession\LogoutReason;
use App\Domain\UserSettings\AppearanceTheme;
use App\Domain\UserSettings\UserSettingsFacade;
use App\Model\Security\Permission\AdminPermission;
use App\Presentation\Accessory\RequiresPermission;
use App\Presentation\Components\Admin\Menu\SidebarMenu\SidebarMenu;
use App\Presentation\Components\Admin\Menu\SidebarMenu\SidebarMenuFactory;
use App\Presentation\Modules\Base\BasePresenter;
use Nette\Http\IResponse;
use Override;
use ReflectionClass;
use ReflectionMethod;

class BaseAdminPresenter extends BasePresenter
{
    private const string THEME_COOKIE = 'admin_theme';

    /**
     * Cíl, kam se posílá uživatel, který přišel o přístup na aktuální stránku.
     * Nesmí vyžadovat nic než admin.access, jinak by vznikla smyčka.
     */
    private const string FALLBACK_PRESENTER = 'Admin:Home';

    private UserSettingsFacade $userSettingsFacade;

    private SidebarMenuFactory $sidebarMenuFactory;

    private ?AppearanceTheme $adminTheme = null;

    public function injectUserSettingsFacade(UserSettingsFacade $userSettingsFacade): void
    {
        $this->userSettingsFacade = $userSettingsFacade;
    }


    public function injectSidebarMenuFactory(SidebarMenuFactory $sidebarMenuFactory): void
    {
        $this->sidebarMenuFactory = $sidebarMenuFactory;
    }


    protected function createComponentSidebarMenu(): SidebarMenu
    {
        return $this->sidebarMenuFactory->create($this->getAdminTheme());
    }


    /**
     * Volá se s třídou presenteru a pak s každou metodou action, render a handle,
     * tedy při každém requestu. Odebrané oprávnění se proto projeví hned
     * u dalšího kliknutí, ne až po odhlášení.
     *
     * @param ReflectionMethod|ReflectionClass $element
     * @return void
     * @phpstan-ignore-next-line
     */
    #[Override]
    public function checkRequirements(ReflectionMethod|ReflectionClass $element): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect(
                destination: ':Admin:Sign:in',
                args: ['key' => $this->storeRequest()]
            );
        }

        if (!$this->getSecurityUser()->isAllowed(AdminPermission::Access)) {
            $this->logOutAccessRevoked();
        }

        $this->checkPermissionAttributes($element);

        parent::checkRequirements($element);
    }


    protected function startup(): void
    {
        parent::startup();

        if ($this->getUser()->isLoggedIn() && $this->getHttpRequest()->getCookie(self::THEME_COOKIE) === null) {
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


    /**
     * @param ReflectionMethod|ReflectionClass<object> $element
     */
    private function checkPermissionAttributes(ReflectionMethod|ReflectionClass $element): void
    {
        foreach ($element->getAttributes(RequiresPermission::class) as $attribute) {
            $permissions = $attribute->newInstance()->permissions;

            // Uvnitř jednoho atributu stačí jedno z uvedených oprávnění.
            foreach ($permissions as $permission) {
                if ($this->getSecurityUser()->isAllowed($permission)) {
                    continue 2;
                }
            }

            $this->denyAccess();
        }
    }


    /**
     * Ztráta přístupu na stránku není chyba uživatele — pošleme ho na dashboard
     * s vysvětlením. Chybová stránka 403 zůstává jen pro případ, kdy by selhal
     * i samotný dashboard, což by jinak znamenalo smyčku přesměrování.
     */
    private function denyAccess(): void
    {
        if ($this->getName() === self::FALLBACK_PRESENTER) {
            $this->error('Insufficient privileges', IResponse::S403_Forbidden);
        }

        $this->flashError('Na tuto sekci nemáte oprávnění.');
        $this->redirect(':' . self::FALLBACK_PRESENTER . ':default');
    }


    /**
     * Bez admin.access se uživatel nemá kam přesměrovat, takže mu nezbývá
     * než vynucené odhlášení.
     */
    private function logOutAccessRevoked(): void
    {
        $this->getSecurityUser()->forceLogout(LogoutReason::AccessRevoked);
        $this->flashError('Váš přístup do administrace byl odebrán.');
        $this->redirect(':Admin:Sign:in');
    }
}
