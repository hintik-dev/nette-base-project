<?php declare(strict_types=1);
namespace App\Domain\UserRole;

use Nette\Database\Table\ActiveRow;

class ExplorerUserRoleMapper
{
    public function mapUserRole(ActiveRow $row): UserRole
    {
        return new UserRole(
            id: $row[ExplorerUserRoleRepository::COLUMN_ID],
            code: $row[ExplorerUserRoleRepository::COLUMN_CODE],
            name: $row[ExplorerUserRoleRepository::COLUMN_NAME],
            description: $row[ExplorerUserRoleRepository::COLUMN_DESCRIPTION] ?? null,
            priority: (int) $row[ExplorerUserRoleRepository::COLUMN_PRIORITY],
            isSystem: (bool) $row[ExplorerUserRoleRepository::COLUMN_IS_SYSTEM],
            createdAt: $row[ExplorerUserRoleRepository::COLUMN_CREATED_AT],
        );
    }


    public function mapRolePermission(ActiveRow $row): RolePermission
    {
        return new RolePermission(
            userRoleId: (int) $row[ExplorerUserRoleRepository::COLUMN_PERMISSION_ROLE_ID],
            permissionKey: $row[ExplorerUserRoleRepository::COLUMN_PERMISSION_KEY],
            effect: PermissionEffect::from($row[ExplorerUserRoleRepository::COLUMN_PERMISSION_EFFECT]),
        );
    }
}
