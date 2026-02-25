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
        $list->addRoute('<presenter>/<action>[/<id>]', 'Home:default');
        return $router;
    }
}
