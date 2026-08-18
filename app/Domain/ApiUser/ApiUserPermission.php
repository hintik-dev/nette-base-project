<?php declare(strict_types=1);
namespace App\Domain\ApiUser;

use App\Model\Security\Permission\PermissionDefinition;
use App\Model\Security\Permission\TPermissionDefinition;

enum ApiUserPermission: string implements PermissionDefinition
{
    use TPermissionDefinition;

    case ListAll = 'api-user.list';
    case Create = 'api-user.create';
    case Edit = 'api-user.edit';
    case Delete = 'api-user.delete';
    case RegenerateToken = 'api-user.regenerate-token';


    public function getLabel(): string
    {
        return match ($this) {
            self::ListAll         => 'Zobrazit seznam API uživatelů',
            self::Create          => 'Založit API uživatele',
            self::Edit            => 'Upravit API uživatele',
            self::Delete          => 'Smazat API uživatele',
            self::RegenerateToken => 'Vygenerovat nový token',
        };
    }


    public function getGroup(): string
    {
        return 'API uživatelé';
    }
}
