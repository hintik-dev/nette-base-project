<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateNotificationTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('notification', ['id' => true, 'primary_key' => 'id', 'comment' => 'Obsah notifikace, sdílený mezi všemi příjemci']);
        $table
            ->addColumn('type', 'string', ['limit' => 50, 'null' => false, 'comment' => 'App\\Domain\\Notification\\NotificationType — plain string, ne DB enum, nové typy nesmí vyžadovat migraci'])
            ->addColumn('title', 'string', ['limit' => 255, 'null' => false, 'comment' => 'Nadpis notifikace'])
            ->addColumn('message', 'text', ['null' => false, 'comment' => 'Text notifikace'])
            ->addColumn('link', 'string', ['limit' => 255, 'null' => true, 'comment' => 'Volitelný cíl (presenter link nebo relativní URL)'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP', 'comment' => 'Čas vytvoření notifikace'])
            ->create();
    }
}
