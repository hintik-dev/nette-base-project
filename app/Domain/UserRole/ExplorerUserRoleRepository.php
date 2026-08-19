<?php declare(strict_types=1);
namespace App\Domain\UserRole;

use App\Core\Database\ExplorerRepository;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use RuntimeException;

class ExplorerUserRoleRepository extends ExplorerRepository
{
    public const string TABLE_NAME = 'user_role';
    public const string TABLE_PERMISSION = 'user_role_permission';
    public const string TABLE_USER_ROLE = 'user_x_user_role';

    public const string COLUMN_ID = 'id';
    public const string COLUMN_CODE = 'code';
    public const string COLUMN_NAME = 'name';
    public const string COLUMN_DESCRIPTION = 'description';
    public const string COLUMN_PRIORITY = 'priority';
    public const string COLUMN_IS_SYSTEM = 'is_system';
    public const string COLUMN_CREATED_AT = 'created_at';

    public const string COLUMN_PERMISSION_ROLE_ID = 'user_role_id';
    public const string COLUMN_PERMISSION_KEY = 'permission_key';
    public const string COLUMN_PERMISSION_EFFECT = 'effect';

    public const string COLUMN_USER_ROLE_USER_ID = 'user_id';
    public const string COLUMN_USER_ROLE_ROLE_ID = 'user_role_id';


    public function __construct(
        private readonly ExplorerUserRoleMapper $userRoleMapper,
    ) {
        parent::__construct(self::TABLE_NAME);
    }


    /** @return Selection<ActiveRow> */
    public function getAllDataSource(): Selection
    {
        return $this->findAll();
    }


    /** @return list<UserRole> */
    public function getAll(): array
    {
        $roles = [];

        foreach ($this->getTable()->order(self::COLUMN_PRIORITY . ' ASC') as $row) {
            $roles[] = $this->userRoleMapper->mapUserRole($row);
        }

        return $roles;
    }


    /**
     * Role, které lze uživateli přiřadit — tedy vše kromě výchozí.
     *
     * @return list<UserRole>
     */
    public function getAssignable(): array
    {
        $roles = [];

        $selection = $this->getTable()
            ->where(self::COLUMN_PRIORITY . ' > ?', UserRole::DEFAULT_PRIORITY)
            ->order(self::COLUMN_PRIORITY . ' ASC');

        foreach ($selection as $row) {
            $roles[] = $this->userRoleMapper->mapUserRole($row);
        }

        return $roles;
    }


    /**
     * @throws UserRoleNotFoundException
     */
    public function getById(int $id): UserRole
    {
        $row = $this->getTable()
            ->where(self::COLUMN_ID, $id)
            ->fetch();

        if ($row === null) {
            throw new UserRoleNotFoundException();
        }

        return $this->userRoleMapper->mapUserRole($row);
    }


    /**
     * @throws UserRoleNotFoundException
     */
    public function getByCode(string $code): UserRole
    {
        $row = $this->getTable()
            ->where(self::COLUMN_CODE, $code)
            ->fetch();

        if ($row === null) {
            throw new UserRoleNotFoundException();
        }

        return $this->userRoleMapper->mapUserRole($row);
    }


    /**
     * @throws UserRoleNotFoundException
     */
    public function getDefaultRole(): UserRole
    {
        $row = $this->getTable()
            ->where(self::COLUMN_PRIORITY, UserRole::DEFAULT_PRIORITY)
            ->fetch();

        if ($row === null) {
            throw new UserRoleNotFoundException();
        }

        return $this->userRoleMapper->mapUserRole($row);
    }


    public function create(string $code, string $name, ?string $description, int $priority, bool $isSystem = false): UserRole
    {
        $row = $this->getTable()->insert([
            self::COLUMN_CODE => $code,
            self::COLUMN_NAME => $name,
            self::COLUMN_DESCRIPTION => $description,
            self::COLUMN_PRIORITY => $priority,
            self::COLUMN_IS_SYSTEM => $isSystem,
        ]);

        if (!($row instanceof ActiveRow)) {
            throw new RuntimeException('Failed to create user role');
        }

        return $this->userRoleMapper->mapUserRole($row);
    }


    public function update(int $id, string $name, ?string $description, int $priority): void
    {
        $this->getTable()
            ->where(self::COLUMN_ID, $id)
            ->update([
                self::COLUMN_NAME => $name,
                self::COLUMN_DESCRIPTION => $description,
                self::COLUMN_PRIORITY => $priority,
            ]);
    }


    public function delete(int $id): void
    {
        $this->getTable()
            ->where(self::COLUMN_ID, $id)
            ->delete();
    }


    public function codeExists(string $code, ?int $excludeId = null): bool
    {
        return $this->existsBy(self::COLUMN_CODE, $code, $excludeId);
    }


    public function priorityExists(int $priority, ?int $excludeId = null): bool
    {
        return $this->existsBy(self::COLUMN_PRIORITY, $priority, $excludeId);
    }


    /**
     * Jediný dotaz, ze kterého se skládají efektivní oprávnění uživatele:
     * záznamy výchozí role plus všech rolí, které uživatel má, seřazené
     * vzestupně podle priority role. Vyhodnocení pak jen jde vrstvu po vrstvě.
     *
     * @return list<array{priority: int, permission_key: string, effect: string}>
     */
    public function getEffectivePermissionRows(int $userId): array
    {
        $sql = sprintf(
            'SELECT r.%1$s AS priority, p.%2$s AS permission_key, p.%3$s AS effect
             FROM %4$s r
             INNER JOIN %5$s p ON p.%6$s = r.%7$s
             WHERE r.%1$s = ? OR r.%7$s IN (SELECT %8$s FROM %9$s WHERE %10$s = ?)
             ORDER BY r.%1$s ASC',
            self::COLUMN_PRIORITY,
            self::COLUMN_PERMISSION_KEY,
            self::COLUMN_PERMISSION_EFFECT,
            self::TABLE_NAME,
            self::TABLE_PERMISSION,
            self::COLUMN_PERMISSION_ROLE_ID,
            self::COLUMN_ID,
            self::COLUMN_USER_ROLE_ROLE_ID,
            self::TABLE_USER_ROLE,
            self::COLUMN_USER_ROLE_USER_ID,
        );

        $rows = [];

        foreach ($this->database->query($sql, UserRole::DEFAULT_PRIORITY, $userId) as $row) {
            $rows[] = [
                'priority' => (int) $row->priority,
                'permission_key' => (string) $row->permission_key,
                'effect' => (string) $row->effect,
            ];
        }

        return $rows;
    }


    /**
     * Oprávnění výchozí role — použije se pro nepřihlášeného uživatele.
     *
     * @return list<array{priority: int, permission_key: string, effect: string}>
     */
    public function getDefaultRolePermissionRows(): array
    {
        $defaultRow = $this->getTable()
            ->where(self::COLUMN_PRIORITY, UserRole::DEFAULT_PRIORITY)
            ->fetch();

        if ($defaultRow === null) {
            return [];
        }

        $rows = [];

        $selection = $this->getTable(self::TABLE_PERMISSION)
            ->where(self::COLUMN_PERMISSION_ROLE_ID, $defaultRow[self::COLUMN_ID]);

        foreach ($selection as $row) {
            $rows[] = [
                'priority' => UserRole::DEFAULT_PRIORITY,
                'permission_key' => (string) $row[self::COLUMN_PERMISSION_KEY],
                'effect' => (string) $row[self::COLUMN_PERMISSION_EFFECT],
            ];
        }

        return $rows;
    }


    /**
     * Explicitní nastavení jedné role, indexované klíčem oprávnění.
     *
     * @return array<string, PermissionEffect>
     */
    public function getPermissionsForRole(int $roleId): array
    {
        $permissions = [];

        $selection = $this->getTable(self::TABLE_PERMISSION)
            ->where(self::COLUMN_PERMISSION_ROLE_ID, $roleId);

        foreach ($selection as $row) {
            $permissions[(string) $row[self::COLUMN_PERMISSION_KEY]] =
                PermissionEffect::from((string) $row[self::COLUMN_PERMISSION_EFFECT]);
        }

        return $permissions;
    }


    /**
     * Přepíše kompletní nastavení role. Klíče, které v poli nejsou,
     * se stanou neutrálními (řádek zmizí).
     *
     * @param array<string, PermissionEffect> $permissions
     */
    public function replacePermissionsForRole(int $roleId, array $permissions): void
    {
        $this->database->beginTransaction();

        try {
            $this->getTable(self::TABLE_PERMISSION)
                ->where(self::COLUMN_PERMISSION_ROLE_ID, $roleId)
                ->delete();

            $insert = [];

            foreach ($permissions as $key => $effect) {
                $insert[] = [
                    self::COLUMN_PERMISSION_ROLE_ID => $roleId,
                    self::COLUMN_PERMISSION_KEY => $key,
                    self::COLUMN_PERMISSION_EFFECT => $effect->value,
                ];
            }

            if ($insert !== []) {
                $this->getTable(self::TABLE_PERMISSION)->insert($insert);
            }

            $this->database->commit();
        } catch (\Throwable $e) {
            $this->database->rollBack();
            throw $e;
        }
    }


    /**
     * Všechny klíče uložené v databázi — pro dohledání osiřelých záznamů.
     *
     * @return list<string>
     */
    public function getAllStoredPermissionKeys(): array
    {
        $keys = [];

        $selection = $this->getTable(self::TABLE_PERMISSION)
            ->select('DISTINCT ' . self::COLUMN_PERMISSION_KEY);

        foreach ($selection as $row) {
            $keys[] = (string) $row[self::COLUMN_PERMISSION_KEY];
        }

        return $keys;
    }


    /** @return list<int> */
    public function getRoleIdsForUser(int $userId): array
    {
        $ids = [];

        $selection = $this->getTable(self::TABLE_USER_ROLE)
            ->where(self::COLUMN_USER_ROLE_USER_ID, $userId);

        foreach ($selection as $row) {
            $ids[] = (int) $row[self::COLUMN_USER_ROLE_ROLE_ID];
        }

        return $ids;
    }


    /**
     * ID uživatelů, kteří mají alespoň jednu z daných rolí (bez duplicit).
     *
     * @param list<int> $roleIds
     * @return list<int>
     */
    public function getUserIdsForRoles(array $roleIds): array
    {
        if ($roleIds === []) {
            return [];
        }

        $ids = [];

        $selection = $this->getTable(self::TABLE_USER_ROLE)
            ->select('DISTINCT ' . self::COLUMN_USER_ROLE_USER_ID)
            ->where(self::COLUMN_USER_ROLE_ROLE_ID, $roleIds);

        foreach ($selection as $row) {
            $ids[] = (int) $row[self::COLUMN_USER_ROLE_USER_ID];
        }

        return $ids;
    }


    /** @return list<UserRole> */
    public function getRolesForUser(int $userId): array
    {
        $roleIds = $this->getRoleIdsForUser($userId);

        if ($roleIds === []) {
            return [];
        }

        $roles = [];

        $selection = $this->getTable()
            ->where(self::COLUMN_ID, $roleIds)
            ->order(self::COLUMN_PRIORITY . ' ASC');

        foreach ($selection as $row) {
            $roles[] = $this->userRoleMapper->mapUserRole($row);
        }

        return $roles;
    }


    /**
     * Názvy rolí všech uživatelů najednou — aby grid nedělal dotaz na řádek.
     *
     * @return array<int, list<string>>
     */
    public function getRoleNamesByUser(): array
    {
        $sql = sprintf(
            'SELECT x.%1$s AS user_id, r.%2$s AS name
             FROM %3$s x
             INNER JOIN %4$s r ON r.%5$s = x.%6$s
             ORDER BY r.%7$s ASC',
            self::COLUMN_USER_ROLE_USER_ID,
            self::COLUMN_NAME,
            self::TABLE_USER_ROLE,
            self::TABLE_NAME,
            self::COLUMN_ID,
            self::COLUMN_USER_ROLE_ROLE_ID,
            self::COLUMN_PRIORITY,
        );

        $byUser = [];

        foreach ($this->database->query($sql) as $row) {
            $byUser[(int) $row->user_id][] = (string) $row->name;
        }

        return $byUser;
    }


    /**
     * Nahradí přiřazení rolí uživateli. Výchozí role se nepřiřazuje,
     * proto se z pole odfiltruje.
     *
     * @param list<int> $roleIds
     */
    public function setRolesForUser(int $userId, array $roleIds): void
    {
        $this->database->beginTransaction();

        try {
            $this->getTable(self::TABLE_USER_ROLE)
                ->where(self::COLUMN_USER_ROLE_USER_ID, $userId)
                ->delete();

            $insert = [];

            foreach (array_unique($roleIds) as $roleId) {
                $insert[] = [
                    self::COLUMN_USER_ROLE_USER_ID => $userId,
                    self::COLUMN_USER_ROLE_ROLE_ID => $roleId,
                ];
            }

            if ($insert !== []) {
                $this->getTable(self::TABLE_USER_ROLE)->insert($insert);
            }

            $this->database->commit();
        } catch (\Throwable $e) {
            $this->database->rollBack();
            throw $e;
        }
    }


    /**
     * Počet uživatelů, kteří mají danou roli — pro varování před smazáním.
     */
    public function countUsersWithRole(int $roleId): int
    {
        return $this->getTable(self::TABLE_USER_ROLE)
            ->where(self::COLUMN_USER_ROLE_ROLE_ID, $roleId)
            ->count('*');
    }


    /**
     * ID rolí, které dané oprávnění explicitně povolují — používá se
     * ke kontrole, že nezmizí poslední role se správou oprávnění.
     *
     * @return list<int>
     */
    public function getRoleIdsGranting(string $permissionKey): array
    {
        $ids = [];

        $selection = $this->getTable(self::TABLE_PERMISSION)
            ->where(self::COLUMN_PERMISSION_KEY, $permissionKey)
            ->where(self::COLUMN_PERMISSION_EFFECT, PermissionEffect::Allow->value);

        foreach ($selection as $row) {
            $ids[] = (int) $row[self::COLUMN_PERMISSION_ROLE_ID];
        }

        return $ids;
    }


    private function existsBy(string $column, mixed $value, ?int $excludeId): bool
    {
        $selection = $this->getTable()
            ->where($column, $value);

        if ($excludeId !== null) {
            $selection->where(self::COLUMN_ID . ' != ?', $excludeId);
        }

        return $selection->count('*') > 0;
    }
}
