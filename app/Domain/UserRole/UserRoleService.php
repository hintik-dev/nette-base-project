<?php declare(strict_types=1);
namespace App\Domain\UserRole;

use App\Model\Security\Permission\PermissionRegistry;

/**
 * Business logika nad rolemi. Bez bezpečnostních kontrol — ty dělá UserRoleFacade.
 */
class UserRoleService
{
    public function __construct(
        private readonly ExplorerUserRoleRepository $userRoleRepository,
        private readonly PermissionRegistry $permissionRegistry,
    ) {
    }


    /** @return list<UserRole> */
    public function getAllRoles(): array
    {
        return $this->userRoleRepository->getAll();
    }


    /** @return list<UserRole> */
    public function getAssignableRoles(): array
    {
        return $this->userRoleRepository->getAssignable();
    }


    /**
     * @throws UserRoleNotFoundException
     */
    public function getRoleById(int $id): UserRole
    {
        return $this->userRoleRepository->getById($id);
    }


    /**
     * @throws UserRoleNotFoundException
     */
    public function getDefaultRole(): UserRole
    {
        return $this->userRoleRepository->getDefaultRole();
    }


    /**
     * @throws DefaultRoleException
     */
    public function createRole(string $code, string $name, ?string $description, int $priority): UserRole
    {
        $this->assertAssignablePriority($priority);

        return $this->userRoleRepository->create($code, $name, $description, $priority);
    }


    /**
     * @throws UserRoleNotFoundException
     * @throws DefaultRoleException
     */
    public function updateRole(int $id, string $name, ?string $description, int $priority): void
    {
        $role = $this->userRoleRepository->getById($id);

        if ($role->isDefault() && $priority !== UserRole::DEFAULT_PRIORITY) {
            throw new DefaultRoleException('Výchozí roli nelze změnit prioritu.');
        }

        if (!$role->isDefault()) {
            $this->assertAssignablePriority($priority);
        }

        $this->userRoleRepository->update($id, $name, $description, $priority);
    }


    /**
     * @throws UserRoleNotFoundException
     * @throws DefaultRoleException
     */
    public function deleteRole(int $id): void
    {
        $role = $this->userRoleRepository->getById($id);

        if ($role->isDefault()) {
            throw new DefaultRoleException('Výchozí roli nelze smazat.');
        }

        if ($role->isSystem) {
            throw new DefaultRoleException('Systémovou roli nelze smazat.');
        }

        $this->userRoleRepository->delete($id);
    }


    public function codeExists(string $code, ?int $excludeId = null): bool
    {
        return $this->userRoleRepository->codeExists($code, $excludeId);
    }


    public function priorityExists(int $priority, ?int $excludeId = null): bool
    {
        return $this->userRoleRepository->priorityExists($priority, $excludeId);
    }


    /**
     * @return array<string, PermissionEffect>
     */
    public function getPermissionsForRole(int $roleId): array
    {
        return $this->userRoleRepository->getPermissionsForRole($roleId);
    }


    /**
     * Přepíše nastavení role. Klíče neznámé registru se zahodí — do databáze
     * se nesmí dostat oprávnění, které nemá kdo kontrolovat.
     *
     * @param array<string, PermissionEffect> $permissions
     */
    public function setPermissionsForRole(int $roleId, array $permissions): void
    {
        $known = array_filter(
            $permissions,
            fn (PermissionEffect $effect, string $key): bool => $this->permissionRegistry->has($key),
            ARRAY_FILTER_USE_BOTH,
        );

        $this->userRoleRepository->replacePermissionsForRole($roleId, $known);
    }


    /** @return list<UserRole> */
    public function getRolesForUser(int $userId): array
    {
        return $this->userRoleRepository->getRolesForUser($userId);
    }


    /** @return list<int> */
    public function getRoleIdsForUser(int $userId): array
    {
        return $this->userRoleRepository->getRoleIdsForUser($userId);
    }


    /**
     * Nastaví uživateli role. Výchozí roli nelze přiřadit — aplikuje se vždy,
     * takže se z požadavku odfiltruje.
     *
     * @param list<int> $roleIds
     */
    public function setRolesForUser(int $userId, array $roleIds): void
    {
        $assignableIds = array_map(
            static fn (UserRole $role): int => $role->id,
            $this->userRoleRepository->getAssignable(),
        );

        $this->userRoleRepository->setRolesForUser(
            $userId,
            array_values(array_intersect($roleIds, $assignableIds)),
        );
    }


    public function countUsersWithRole(int $roleId): int
    {
        return $this->userRoleRepository->countUsersWithRole($roleId);
    }


    /**
     * Klíče v databázi, které registr nezná — zbytky po refaktoringu kódu.
     *
     * @return list<string>
     */
    public function getOrphanPermissionKeys(): array
    {
        return $this->permissionRegistry->findOrphanKeys(
            $this->userRoleRepository->getAllStoredPermissionKeys(),
        );
    }


    /**
     * Ověří, že po zásahu zůstane aspoň jedna role udělující dané oprávnění.
     * Používá se jako pojistka proti zamčení se ven ze správy oprávnění.
     *
     * @param list<int> $roleIdsBeingRevoked
     */
    public function isLastRoleGranting(string $permissionKey, array $roleIdsBeingRevoked): bool
    {
        $granting = $this->userRoleRepository->getRoleIdsGranting($permissionKey);

        return array_diff($granting, $roleIdsBeingRevoked) === [];
    }


    /**
     * @throws DefaultRoleException
     */
    private function assertAssignablePriority(int $priority): void
    {
        if ($priority <= UserRole::DEFAULT_PRIORITY) {
            throw new DefaultRoleException('Priorita běžné role musí být větší než ' . UserRole::DEFAULT_PRIORITY . '.');
        }
    }
}
