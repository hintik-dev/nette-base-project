<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSuperadminUserRole extends AbstractMigration
{
    public function up(): void
    {
        $this->table('user')
            ->changeColumn('role', 'enum', ['values' => ['superadmin', 'admin', 'user'], 'null' => false])
            ->update();
    }


    public function down(): void
    {
        // Bez přeřazení by MySQL hodnotu mimo enum přepsalo na prázdný řetězec
        $this->execute("UPDATE `user` SET `role` = 'admin' WHERE `role` = 'superadmin'");

        $this->table('user')
            ->changeColumn('role', 'enum', ['values' => ['admin', 'user'], 'null' => false])
            ->update();
    }
}
