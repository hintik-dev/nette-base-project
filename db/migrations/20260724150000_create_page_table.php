<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePageTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('page', ['id' => true, 'primary_key' => 'id']);
        $table
            ->addColumn('slug', 'string', ['limit' => 190, 'null' => false, 'comment' => 'URL slug (jeden segment cesty, bez lomítek)'])
            ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('content', 'json', ['null' => false, 'comment' => 'Puck data payload: {content: [{type, props}], root: {props}}'])
            ->addColumn('status', 'enum', ['values' => ['draft', 'published'], 'null' => false, 'default' => 'draft'])
            ->addColumn('published_at', 'datetime', ['null' => true, 'comment' => 'Čas posledního publikování'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP', 'comment' => 'Datum vytvoření záznamu'])
            ->addIndex('slug', ['unique' => true])
            ->create();
    }
}
