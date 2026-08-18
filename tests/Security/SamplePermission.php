<?php declare(strict_types=1);
namespace Tests\Security;

use App\Model\Security\Permission\PermissionDefinition;
use App\Model\Security\Permission\TPermissionDefinition;

/**
 * Vlastnická oprávnění zatím žádná doména nepoužívá, ale rozklad klíče na
 * resource a scope je to, na čem stojí dohledání resolveru v isAllowedOn().
 * Tenhle enum proto tvar klíče ověřuje nezávisle na tom, kdo ho použije první.
 */
enum SamplePermission: string implements PermissionDefinition
{
    use TPermissionDefinition;

    case Edit = 'sample.edit';
    case EditOwn = 'sample.edit.own';
    case ChangePassword = 'sample.change-password';


    public function getLabel(): string
    {
        return 'Ukázka';
    }


    public function getGroup(): string
    {
        return 'Ukázka';
    }
}
