<?php declare(strict_types=1);
namespace App\Domain\UserRole;

/**
 * Efekt oprávnění v rámci jedné role.
 *
 * Třetí stav — neutrální — se v databázi neukládá, je to absence záznamu.
 */
enum PermissionEffect: string
{
    case Allow = 'allow';
    case Deny = 'deny';


    public function getLabel(): string
    {
        return match ($this) {
            self::Allow => 'Povoleno',
            self::Deny  => 'Blokováno',
        };
    }
}
