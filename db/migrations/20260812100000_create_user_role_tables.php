<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUserRoleTables extends AbstractMigration
{
    public function change(): void
    {
        $this->table('user_role', ['id' => true, 'primary_key' => 'id'])
            ->addColumn('code', 'string', ['limit' => 64, 'null' => false, 'comment' => 'Strojový klíč role (default, editor, ...)'])
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false, 'comment' => 'Zobrazovaný název role'])
            ->addColumn('description', 'string', ['limit' => 255, 'null' => true, 'comment' => 'Popis role pro administrátory'])
            ->addColumn('priority', 'integer', ['null' => false, 'signed' => false, 'comment' => 'Priorita skládání oprávnění; 0 = výchozí role, vyšší přepisuje nižší'])
            ->addColumn('is_system', 'boolean', ['null' => false, 'default' => false, 'comment' => 'Systémová role — nelze smazat ani přejmenovat'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP', 'comment' => 'Datum vytvoření záznamu'])
            ->addIndex('code', ['unique' => true])
            ->addIndex('priority', ['unique' => true])
            ->create();

        // Neexistence řádku = neutrální stav. Ukládají se jen explicitní allow/deny.
        $this->table('user_role_permission', ['id' => true, 'primary_key' => 'id'])
            ->addColumn('user_role_id', 'integer', ['null' => false, 'signed' => false, 'comment' => 'FK App\\Domain\\UserRole\\UserRole::id'])
            ->addColumn('permission_key', 'string', ['limit' => 190, 'null' => false, 'comment' => 'Klíč z PermissionRegistry, např. page.edit — bez FK, zdrojem pravdy je kód'])
            ->addColumn('effect', 'enum', ['values' => ['allow', 'deny'], 'null' => false, 'comment' => 'Efekt oprávnění v této roli'])
            ->addIndex(['user_role_id', 'permission_key'], ['unique' => true])
            ->addForeignKey('user_role_id', 'user_role', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();

        $this->table('user_x_user_role', ['id' => false, 'primary_key' => ['user_id', 'user_role_id']])
            ->addColumn('user_id', 'integer', ['null' => false, 'signed' => false, 'comment' => 'FK App\\Domain\\User\\User::id'])
            ->addColumn('user_role_id', 'integer', ['null' => false, 'signed' => false, 'comment' => 'FK App\\Domain\\UserRole\\UserRole::id'])
            ->addIndex('user_role_id')
            ->addForeignKey('user_id', 'user', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addForeignKey('user_role_id', 'user_role', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();
    }
}
