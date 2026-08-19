<?php declare(strict_types=1);
namespace App\Domain\Notification;

use App\Model\Security\Permission\PermissionDefinition;
use App\Model\Security\Permission\TPermissionDefinition;

enum NotificationPermission: string implements PermissionDefinition
{
    use TPermissionDefinition;

    case Send = 'notification.send';


    public function getLabel(): string
    {
        return match ($this) {
            self::Send => 'Odeslat hromadnou notifikaci',
        };
    }


    public function getGroup(): string
    {
        return 'Notifikace';
    }
}
