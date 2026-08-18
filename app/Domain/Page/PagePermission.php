<?php declare(strict_types=1);
namespace App\Domain\Page;

use App\Model\Security\Permission\PermissionDefinition;
use App\Model\Security\Permission\TPermissionDefinition;

enum PagePermission: string implements PermissionDefinition
{
    use TPermissionDefinition;

    case ListAll = 'page.list';
    case Create = 'page.create';
    case Edit = 'page.edit';
    case Publish = 'page.publish';


    public function getLabel(): string
    {
        return match ($this) {
            self::ListAll => 'Zobrazit seznam stránek',
            self::Create  => 'Založit stránku',
            self::Edit    => 'Upravit stránku',
            self::Publish => 'Publikovat stránku',
        };
    }


    public function getGroup(): string
    {
        return 'Stránky';
    }
}
