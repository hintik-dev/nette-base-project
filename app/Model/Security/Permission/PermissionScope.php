<?php declare(strict_types=1);
namespace App\Model\Security\Permission;

/**
 * Vazba oprávnění na vlastnost konkrétní entity. Klíč `page.edit.own` znamená
 * „upravit stránku, jejímž je uživatel autorem“ — samotné ACL to nerozhodne,
 * potřebuje k tomu entitu a ScopeResolver.
 */
enum PermissionScope: string
{
    case Own = 'own';


    public function getLabel(): string
    {
        return match ($this) {
            self::Own => 'jen vlastní',
        };
    }
}
