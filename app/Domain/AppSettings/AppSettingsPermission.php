<?php declare(strict_types=1);
namespace App\Domain\AppSettings;

use App\Model\Security\Permission\PermissionDefinition;
use App\Model\Security\Permission\TPermissionDefinition;

enum AppSettingsPermission: string implements PermissionDefinition
{
    use TPermissionDefinition;

    case View = 'app-settings.view';
    case Edit = 'app-settings.edit';


    public function getLabel(): string
    {
        return match ($this) {
            self::View => 'Zobrazit nastavení aplikace',
            self::Edit => 'Upravit nastavení aplikace',
        };
    }


    public function getGroup(): string
    {
        return 'Nastavení aplikace';
    }
}
