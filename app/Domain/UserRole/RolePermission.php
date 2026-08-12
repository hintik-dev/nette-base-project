<?php declare(strict_types=1);
namespace App\Domain\UserRole;

/**
 * Jeden explicitní záznam oprávnění v roli.
 */
readonly class RolePermission
{
    public function __construct(
        public int $userRoleId,
        public string $permissionKey,
        public PermissionEffect $effect,
    ) {
    }
}
