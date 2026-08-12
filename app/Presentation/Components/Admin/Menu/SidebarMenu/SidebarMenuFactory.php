<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\Menu\SidebarMenu;

use App\Domain\UserSettings\AppearanceTheme;

interface SidebarMenuFactory
{
    public function create(AppearanceTheme $adminTheme): SidebarMenu;
}
