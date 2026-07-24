<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateValueStorageTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('value_storage', ['id' => true, 'primary_key' => 'id']);
        $table
            ->addColumn('category', 'string', ['limit' => 100, 'null' => false, 'comment' => 'Skupina nastavení, např. app_settings'])
            ->addColumn('storage_key', 'string', ['limit' => 100, 'null' => false, 'comment' => 'Klíč v rámci skupiny'])
            ->addColumn('value', 'text', ['null' => true, 'comment' => 'Hodnota (vždy string, konverze na straně aplikace)'])
            ->addIndex(['category', 'storage_key'], ['unique' => true])
            ->create();
    }
}
