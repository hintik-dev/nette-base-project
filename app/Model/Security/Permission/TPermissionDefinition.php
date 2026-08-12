<?php declare(strict_types=1);
namespace App\Model\Security\Permission;

/**
 * Společná implementace odvoditelných částí PermissionDefinition.
 * Enum tak musí dodat už jen getLabel() a getGroup().
 *
 * @mixin PermissionDefinition
 */
trait TPermissionDefinition
{
    public function getKey(): string
    {
        return $this->value;
    }


    /**
     * Scope je poslední segment klíče, pokud odpovídá některé hodnotě
     * PermissionScope. Klíče jako `acl.role.edit` tak zůstávají globální.
     */
    public function getScope(): ?PermissionScope
    {
        $segments = explode('.', $this->value);

        if (count($segments) < 3) {
            return null;
        }

        return PermissionScope::tryFrom((string) end($segments));
    }
}
