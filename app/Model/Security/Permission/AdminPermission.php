<?php declare(strict_types=1);
namespace App\Model\Security\Permission;

/**
 * Systémová oprávnění, která nepatří žádné doméně.
 *
 * `admin.access` je zvláštní: bez něj se uživatel nemá kam přesměrovat, proto
 * se kontroluje jak při přihlášení, tak u každého requestu — a jeho ztráta
 * vede k vynucenému odhlášení.
 */
enum AdminPermission: string implements PermissionDefinition
{
    use TPermissionDefinition;

    case Access = 'admin.access';


    public function getLabel(): string
    {
        return match ($this) {
            self::Access => 'Přístup do administrace',
        };
    }


    public function getGroup(): string
    {
        return 'Administrace';
    }
}
