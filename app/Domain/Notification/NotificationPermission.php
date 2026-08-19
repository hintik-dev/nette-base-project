<?php declare(strict_types=1);
namespace App\Domain\Notification;

use App\Model\Security\Permission\PermissionDefinition;
use App\Model\Security\Permission\TPermissionDefinition;

enum NotificationPermission: string implements PermissionDefinition
{
    use TPermissionDefinition;

    case ListAll = 'notification.list_all';
    case Send = 'notification.send';


    public function getLabel(): string
    {
        return match ($this) {
            self::ListAll => 'Zobrazit všechny notifikace v systému',
            self::Send    => 'Odeslat hromadnou notifikaci',
        };
    }


    public function getGroup(): string
    {
        return 'Notifikace';
    }
}
