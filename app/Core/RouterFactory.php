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

        // Presenter musí být vyjmenován (regex "home" odpovídá URL segmentu, ne
        // názvu třídy), jinak by tuto trasu zabralo cokoliv před níže uvedeným
        // slugem stránky z CMS (Page:default). Při přidání nového presenteru
        // do Web modulu je potřeba ho sem doplnit.
        $list->addRoute('<presenter=Home home>/<action>[/<id>]', 'Home:default');
        $list->addRoute('<slug [a-z0-9\-]+>', 'Page:default');

        return $router;
    }
}
