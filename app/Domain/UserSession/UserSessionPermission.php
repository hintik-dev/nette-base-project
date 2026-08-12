<?php declare(strict_types=1);
namespace App\Domain\UserSession;

use App\Model\Security\Permission\PermissionDefinition;
use App\Model\Security\Permission\TPermissionDefinition;

enum UserSessionPermission: string implements PermissionDefinition
{
    use TPermissionDefinition;

    case ListAll = 'user-session.list';
    case Terminate = 'user-session.terminate';


    public function getLabel(): string
    {
        return match ($this) {
            self::ListAll   => 'Zobrazit session uživatelů',
            self::Terminate => 'Vynuceně odhlásit uživatele',
        };
    }


    public function getGroup(): string
    {
        return 'Session uživatelů';
    }
}
