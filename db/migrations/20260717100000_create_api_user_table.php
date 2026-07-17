<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateApiUserTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('api_user', ['id' => true, 'primary_key' => 'id']);
        $table
            ->addColumn('name', 'string', ['limit' => 100, 'null' => false, 'comment' => 'Název API uživatele'])
            ->addColumn('token', 'string', ['limit' => 64, 'null' => false, 'comment' => 'API token (Bearer)'])
            ->addColumn('description', 'text', ['null' => true, 'comment' => 'Volitelný popis'])
            ->addColumn('is_active', 'boolean', ['null' => false, 'default' => true, 'comment' => 'Aktivní/neaktivní'])
            ->addColumn('valid_from', 'datetime', ['null' => true, 'default' => null, 'comment' => 'Platnost od, null = bez omezení'])
            ->addColumn('valid_to', 'datetime', ['null' => true, 'default' => null, 'comment' => 'Platnost do, null = bez omezení'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP', 'comment' => 'Datum vytvoření záznamu'])
            ->addColumn('deleted_at', 'datetime', ['null' => true, 'default' => null, 'comment' => 'Soft delete'])
            ->addIndex('token', ['unique' => true])
            ->create();
    }
}
