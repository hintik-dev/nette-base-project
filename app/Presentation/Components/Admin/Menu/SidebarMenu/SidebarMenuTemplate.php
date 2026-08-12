<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\Menu\SidebarMenu;

use App\Domain\UserSettings\AppearanceTheme;
use App\Presentation\Components\Admin\Menu\MenuSection;
use App\Model\Latte\BaseTemplate;

final class SidebarMenuTemplate extends BaseTemplate
{
    /** Už profiltrované podle oprávnění — šablona nic nerozhoduje. */
    /** @var list<MenuSection> */
    public array $sections = [];

    public AppearanceTheme $adminTheme = AppearanceTheme::Light;
}
