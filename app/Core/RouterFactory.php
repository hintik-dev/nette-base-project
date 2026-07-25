<?php declare(strict_types=1);
namespace App\Core;

use Nette;
use Nette\Application\Routers\RouteList;

final class RouterFactory
{
    use Nette\StaticClass;

    public static function createRouter(): RouteList
    {
        $router = new RouteList();

        self::createAdminRouter($router);
        self::createWebRouter($router);

        return $router;
    }

    public static function createAdminRouter(RouteList $router): RouteList
    {
        $router[] = $list = new RouteList('Admin');
        $list->addRoute('admin/<presenter>/<action>[/<id>]', 'Home:default');
        return $router;
    }

    public static function createWebRouter(RouteList $router): RouteList
    {
        $router[] = $list = new RouteList('Web');

        // Web modul má jediný presenter — Page. Homepage je stránka
        // s prázdným slugem (slug='' odpovídá kořenovému '/').
        $list->addRoute('<slug= [a-z0-9\-]*>', 'Page:default');

        return $router;
    }
}
