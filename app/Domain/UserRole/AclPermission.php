<?php declare(strict_types=1);
namespace App\Domain\UserRole;

use App\Model\Security\Permission\PermissionDefinition;
use App\Model\Security\Permission\TPermissionDefinition;

enum AclPermission: string implements PermissionDefinition
{
    use TPermissionDefinition;

    case RoleList = 'acl.role.list';
    case RoleEdit = 'acl.role.edit';
    case RoleAssign = 'acl.role.assign';


    public function getLabel(): string
    {
        return match ($this) {
            self::RoleList   => 'Zobrazit role',
            self::RoleEdit   => 'Spravovat role a jejich oprávnění',
            self::RoleAssign => 'Přiřazovat role uživatelům',
        };
    }


    public function getGroup(): string
    {
        return 'Role a oprávnění';
    }
}
