<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Převádí statické ACL (StaticAuthorizator) do databáze tak, aby se navenek
 * nic nezměnilo.
 *
 * Původní stav: allow() pro všechny role, jedinou výjimkou byla správa stránek —
 * role `user` na ni neměla, `admin` a `superadmin` ano.
 *
 * Nový stav:
 *  - výchozí role (priorita 0) dostane vše kromě `page.*` a `acl.*`
 *  - role Administrátor (priorita 10) dostane `page.*` a `acl.*`
 *  - uživatelé s rolí admin/superadmin dostanou roli Administrátor
 *  - uživatelé s rolí superadmin dostanou bypass `is_superadmin`
 *
 * Správa oprávnění (`acl.*`) je nová a nikdo ji dřív neměl; dostává ji role
 * Administrátor, aby aplikace zůstala spravovatelná i bez superadmina.
 *
 * Klíče jsou schválně zapsané natvrdo, ne přes PermissionRegistry — migrace
 * musí zůstat zmrazená v čase i po pozdějším refaktoringu oprávnění.
 */
final class SeedUserRolesFromStaticAcl extends AbstractMigration
{
    private const string DEFAULT_ROLE_CODE = 'default';
    private const string ADMIN_ROLE_CODE = 'admin';

    /** Oprávnění, která podle původního ACL měli všichni přihlášení uživatelé. */
    private const array DEFAULT_ROLE_KEYS = [
        'admin.access',
        'user.list',
        'user.detail',
        'user.create',
        'user.edit',
        'user.change-password',
        'user-session.list',
        'user-session.terminate',
        'email.list',
        'email.detail',
        'email.send',
        'email.resend',
        'scheduled-job.list',
        'scheduled-job.edit',
        'scheduled-job.run-now',
        'scheduled-job.run-history',
        'api-user.list',
        'api-user.create',
        'api-user.edit',
        'api-user.delete',
        'api-user.regenerate-token',
        'app-settings.view',
        'app-settings.edit',
        'value-storage.edit',
    ];

    /** Správa obsahu a nově i správa oprávnění — dřív jen pro admin/superadmin. */
    private const array ADMIN_ROLE_KEYS = [
        'page.list',
        'page.create',
        'page.edit',
        'page.publish',
        'acl.role.list',
        'acl.role.edit',
        'acl.role.assign',
    ];


    public function up(): void
    {
        $this->table('user_role')->insert([
            [
                'code' => self::DEFAULT_ROLE_CODE,
                'name' => 'Výchozí role',
                'description' => 'Platí všem uživatelům včetně těch bez jakékoli role. Nelze ji přiřadit ani smazat.',
                'priority' => 0,
                'is_system' => true,
            ],
            [
                'code' => self::ADMIN_ROLE_CODE,
                'name' => 'Administrátor',
                'description' => 'Správa obsahu a oprávnění.',
                'priority' => 10,
                'is_system' => false,
            ],
        ])->saveData();

        $defaultRoleId = $this->getRoleId(self::DEFAULT_ROLE_CODE);
        $adminRoleId = $this->getRoleId(self::ADMIN_ROLE_CODE);

        $this->insertPermissions($defaultRoleId, self::DEFAULT_ROLE_KEYS);
        $this->insertPermissions($adminRoleId, self::ADMIN_ROLE_KEYS);

        // Držitelé původních rolí admin a superadmin dostanou roli Administrátor.
        $this->execute(sprintf(
            'INSERT INTO user_x_user_role (user_id, user_role_id)
             SELECT id, %d FROM user WHERE role IN (\'admin\', \'superadmin\')',
            $adminRoleId,
        ));

        // Superadmin nově znamená bypass ACL, ne roli.
        $this->execute('UPDATE user SET is_superadmin = 1 WHERE role = \'superadmin\'');

        $this->table('user')->removeColumn('role')->update();
    }


    public function down(): void
    {
        $this->table('user')
            ->addColumn('role', 'enum', ['values' => ['user', 'admin', 'superadmin'], 'null' => false, 'default' => 'user'])
            ->update();

        $this->execute('UPDATE user SET role = \'superadmin\' WHERE is_superadmin = 1');

        $adminRoleId = $this->getRoleId(self::ADMIN_ROLE_CODE);

        if ($adminRoleId !== null) {
            $this->execute(sprintf(
                'UPDATE user SET role = \'admin\'
                 WHERE is_superadmin = 0 AND id IN (SELECT user_id FROM user_x_user_role WHERE user_role_id = %d)',
                $adminRoleId,
            ));
        }

        $this->execute('DELETE FROM user_role');
    }


    /**
     * @param list<string> $keys
     */
    private function insertPermissions(?int $roleId, array $keys): void
    {
        if ($roleId === null || $keys === []) {
            return;
        }

        $rows = [];

        foreach ($keys as $key) {
            $rows[] = [
                'user_role_id' => $roleId,
                'permission_key' => $key,
                'effect' => 'allow',
            ];
        }

        $this->table('user_role_permission')->insert($rows)->saveData();
    }


    private function getRoleId(string $code): ?int
    {
        $row = $this->fetchRow(sprintf('SELECT id FROM user_role WHERE code = \'%s\'', $code));

        return $row === false || $row === null ? null : (int) $row['id'];
    }
}
