<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSentEmailTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('sent_email', ['id' => true, 'primary_key' => 'id']);
        $table
            ->addColumn('recipient', 'string', ['limit' => 255, 'null' => false, 'comment' => 'E-mailová adresa příjemce'])
            ->addColumn('subject', 'string', ['limit' => 500, 'null' => false, 'comment' => 'Předmět e-mailu'])
            ->addColumn('body_html', 'text', ['null' => true, 'comment' => 'HTML obsah e-mailu'])
            ->addColumn('status', 'enum', [
                'values' => ['pending', 'sent', 'failed'],
                'null' => false,
                'default' => 'pending',
                'comment' => 'Stav odeslání',
            ])
            ->addColumn('error', 'text', ['null' => true, 'comment' => 'Chybová zpráva při neúspěšném odeslání'])
            ->addColumn('sent_at', 'datetime', ['null' => true, 'comment' => 'Čas úspěšného odeslání'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP', 'comment' => 'Datum vytvoření záznamu'])
            ->addIndex('recipient')
            ->addIndex('status')
            ->addIndex('created_at')
            ->create();
    }
}
