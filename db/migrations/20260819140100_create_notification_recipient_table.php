<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateNotificationRecipientTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('notification_recipient', ['id' => true, 'primary_key' => 'id', 'comment' => 'Stav notifikace pro konkrétního příjemce (přečteno/skryto)']);
        $table
            ->addColumn('notification_id', 'integer', ['null' => false, 'signed' => false, 'comment' => 'FK App\\Domain\\Notification\\Notification::id'])
            ->addColumn('user_id', 'integer', ['null' => false, 'comment' => 'FK App\\Domain\\User\\User::id'])
            ->addColumn('read_at', 'datetime', ['null' => true, 'default' => null, 'comment' => 'Čas přečtení, null = nepřečteno'])
            ->addColumn('hidden_at', 'datetime', ['null' => true, 'default' => null, 'comment' => 'Čas skrytí ("vyčištění schránky"), null = viditelné'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP', 'comment' => 'Čas vzniku záznamu, pro retenční úlohu'])
            ->addIndex(['user_id', 'hidden_at', 'read_at'])
            ->addIndex('notification_id')
            ->addForeignKey('notification_id', 'notification', 'id', ['delete' => 'CASCADE'])
            ->create();
    }
}
