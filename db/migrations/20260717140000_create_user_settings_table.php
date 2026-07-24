<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUserSettingsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('user_settings', ['id' => true, 'primary_key' => 'id']);
        $table
            ->addColumn('user_id', 'integer', ['null' => false, 'signed' => false, 'comment' => 'FK App\\Domain\\User\\User::id'])
            ->addColumn('theme', 'enum', ['values' => ['light', 'dark', 'system'], 'null' => false, 'default' => 'light', 'comment' => 'Preferovaný motiv vzhledu'])
            ->addIndex('user_id', ['unique' => true])
            ->addForeignKey('user_id', 'user', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();
    }
}
