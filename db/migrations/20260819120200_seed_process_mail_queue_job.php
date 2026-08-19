<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SeedProcessMailQueueJob extends AbstractMigration
{
    public function up(): void
    {
        $this->table('scheduled_job')
            ->insert([
                [
                    'name' => 'Zpracování fronty e-mailů',
                    'class' => 'App\\Scheduler\\ProcessMailQueueJob',
                    'description' => 'Zpracovává frontu mail_queue — odesílá čekající e-maily a archivuje výsledek do sent_email.',
                    'cron' => '* * * * *',
                    'is_active' => true,
                    'created_at' => date('Y-m-d H:i:s'),
                ],
            ])
            ->saveData();
    }

    public function down(): void
    {
        $this->execute(<<<SQL
            DELETE FROM scheduled_job WHERE class = 'App\\Scheduler\\ProcessMailQueueJob';
            SQL);
    }
}
