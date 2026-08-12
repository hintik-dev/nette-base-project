<?php declare(strict_types=1);
namespace App\Domain\User;

use App\Model\Security\Permission\PermissionDefinition;
use App\Model\Security\Permission\TPermissionDefinition;

enum UserPermission: string implements PermissionDefinition
{
    use TPermissionDefinition;

    case ListAll = 'user.list';
    case Detail = 'user.detail';
    case Create = 'user.create';
    case Edit = 'user.edit';
    case ChangePassword = 'user.change-password';


    public function getLabel(): string
    {
        return match ($this) {
            self::ListAll        => 'Zobrazit seznam uživatelů',
            self::Detail         => 'Zobrazit detail uživatele',
            self::Create         => 'Založit uživatele',
            self::Edit           => 'Upravit uživatele',
            self::ChangePassword => 'Změnit heslo jinému uživateli',
        };
    }


    public function getGroup(): string
    {
        return 'Uživatelé';
    }
}
