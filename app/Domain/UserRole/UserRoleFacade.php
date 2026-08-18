<?php declare(strict_types=1);
namespace App\Domain\UserRole;

use App\Model\Security\Authorizator\InsufficientPrivilegesException;
use App\Model\Security\Permission\PermissionDefinition;
use App\Model\Security\Permission\PermissionEvaluator;
use App\Model\Security\Permission\PermissionRegistry;
use App\Model\Security\SecurityUser;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

/**
 * Vstupní bod pro správu rolí. Kromě kontroly oprávnění hlídá i pojistky proti
 * zamčení se ven — viz assertNotLosingAclControl().
 */
class UserRoleFacade
{
    public function __construct(
        private readonly UserRoleService $userRoleService,
        private readonly ExplorerUserRoleRepository $userRoleRepository,
        private readonly PermissionRegistry $permissionRegistry,
        private readonly PermissionEvaluator $permissionEvaluator,
        private readonly SecurityUser $securityUser,
    ) {
    }


    /**
     * @throws InsufficientPrivilegesException
     * @return Selection<ActiveRow>
     */
    public function getAllRolesDataSource(): Selection
    {
        $this->assertAllowed(AclPermission::RoleList);

        return $this->userRoleRepository->getAllDataSource();
    }


    /**
     * @throws InsufficientPrivilegesException
     * @return list<UserRole>
     */
    public function getAllRoles(): array
    {
        $this->assertAllowed(AclPermission::RoleList);

        return $this->userRoleService->getAllRoles();
    }


    /**
     * Role, které lze uživateli přiřadit — výchozí role mezi nimi není,
     * protože se aplikuje vždy.
     *
     * @throws InsufficientPrivilegesException
     * @return list<UserRole>
     */
    public function getAssignableRoles(): array
    {
        $this->assertAllowed(AclPermission::RoleAssign);

        return $this->userRoleService->getAssignableRoles();
    }


    /**
     * @throws InsufficientPrivilegesException
     * @throws UserRoleNotFoundException
     */
    public function getRoleById(int $id): UserRole
    {
        $this->assertAllowed(AclPermission::RoleList);

        return $this->userRoleService->getRoleById($id);
    }


    /**
     * @throws InsufficientPrivilegesException
     * @throws DefaultRoleException
     * @throws UserRoleConflictException
     */
    public function createRole(UserRoleFormData $data): UserRole
    {
        $this->assertAllowed(AclPermission::RoleEdit);

        $role = $this->userRoleService->createRole($data);
        $this->permissionEvaluator->clearCache();

        return $role;
    }


    /**
     * @throws InsufficientPrivilegesException
     * @throws UserRoleNotFoundException
     * @throws DefaultRoleException
     * @throws UserRoleConflictException
     */
    public function updateRole(int $id, UserRoleFormData $data): void
    {
        $this->assertAllowed(AclPermission::RoleEdit);

        $this->userRoleService->updateRole($id, $data);
        $this->permissionEvaluator->clearCache();
    }


    /**
     * @throws InsufficientPrivilegesException
     * @throws UserRoleNotFoundException
     * @throws DefaultRoleException
     */
    public function deleteRole(int $id): void
    {
        $this->assertAllowed(AclPermission::RoleEdit);
        $this->assertNotLosingAclControl([$id]);

        $this->userRoleService->deleteRole($id);
        $this->permissionEvaluator->clearCache();
    }


    /**
     * @throws InsufficientPrivilegesException
     * @return array<string, PermissionEffect>
     */
    public function getPermissionsForRole(int $roleId): array
    {
        $this->assertAllowed(AclPermission::RoleList);

        return $this->userRoleService->getPermissionsForRole($roleId);
    }


    /**
     * Přepíše nastavení role. Klíče, které v poli nejsou, se stanou neutrálními.
     *
     * @param array<string, PermissionEffect> $permissions
     * @throws InsufficientPrivilegesException
     * @throws DefaultRoleException
     */
    public function setPermissionsForRole(int $roleId, array $permissions): void
    {
        $this->assertAllowed(AclPermission::RoleEdit);

        $keepsAclControl = ($permissions[AclPermission::RoleEdit->getKey()] ?? null) === PermissionEffect::Allow;

        if (!$keepsAclControl) {
            $this->assertNotLosingAclControl([$roleId]);
        }

        $this->userRoleService->setPermissionsForRole($roleId, $permissions);
        $this->permissionEvaluator->clearCache();
    }


    /**
     * @throws InsufficientPrivilegesException
     * @return list<int>
     */
    public function getRoleIdsForUser(int $userId): array
    {
        $this->assertAllowed(AclPermission::RoleAssign);

        return $this->userRoleService->getRoleIdsForUser($userId);
    }


    /**
     * @throws InsufficientPrivilegesException
     * @return list<UserRole>
     */
    public function getRolesForUser(int $userId): array
    {
        $this->assertAllowed(AclPermission::RoleAssign);

        return $this->userRoleService->getRolesForUser($userId);
    }


    /**
     * @param list<int> $roleIds
     * @throws InsufficientPrivilegesException
     */
    public function setRolesForUser(int $userId, array $roleIds): void
    {
        $this->assertAllowed(AclPermission::RoleAssign);

        $this->userRoleService->setRolesForUser($userId, $roleIds);
        $this->permissionEvaluator->clearCache();
    }


    public function codeExists(string $code, ?int $excludeId = null): bool
    {
        return $this->userRoleService->codeExists($code, $excludeId);
    }


    public function priorityExists(int $priority, ?int $excludeId = null): bool
    {
        return $this->userRoleService->priorityExists($priority, $excludeId);
    }


    public function countUsersWithRole(int $roleId): int
    {
        return $this->userRoleService->countUsersWithRole($roleId);
    }


    /**
     * @throws InsufficientPrivilegesException
     * @return list<string>
     */
    public function getOrphanPermissionKeys(): array
    {
        $this->assertAllowed(AclPermission::RoleList);

        return $this->userRoleService->getOrphanPermissionKeys();
    }


    public function getPermissionRegistry(): PermissionRegistry
    {
        return $this->permissionRegistry;
    }


    /**
     * Nedovolí odebrat správu oprávnění poslední roli, která ji uděluje.
     * Superadmin má vlastní cestu zpět (bypass + CLI), takže se ho to netýká.
     *
     * @param list<int> $roleIdsBeingRevoked
     * @throws InsufficientPrivilegesException
     */
    private function assertNotLosingAclControl(array $roleIdsBeingRevoked): void
    {
        if ($this->securityUser->isSuperadmin()) {
            return;
        }

        if ($this->userRoleService->isLastRoleGranting(AclPermission::RoleEdit->getKey(), $roleIdsBeingRevoked)) {
            throw new InsufficientPrivilegesException();
        }
    }


    /**
     * @throws InsufficientPrivilegesException
     */
    private function assertAllowed(PermissionDefinition $permission): void
    {
        if (!$this->securityUser->isAllowed($permission)) {
            throw new InsufficientPrivilegesException();
        }
    }
}
