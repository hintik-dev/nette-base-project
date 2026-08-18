<?php declare(strict_types=1);
namespace App\Domain\ValueStorage;

use App\Model\Security\Permission\PermissionDefinition;
use App\Model\Security\Permission\TPermissionDefinition;

enum ValueStoragePermission: string implements PermissionDefinition
{
    use TPermissionDefinition;

    case Edit = 'value-storage.edit';


    public function getLabel(): string
    {
        return match ($this) {
            self::Edit => 'Zapisovat do úložiště hodnot',
        };
    }


    public function getGroup(): string
    {
        return 'Úložiště hodnot';
    }
}
