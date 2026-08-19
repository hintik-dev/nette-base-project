<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SeedPruneNotificationsJob extends AbstractMigration
{
    public function up(): void
    {
        $this->table('scheduled_job')
            ->insert([
                [
                    'name' => 'Úklid starých notifikací',
                    'class' => 'App\\Scheduler\\PruneNotificationsJob',
                    'description' => 'Maže přečtené/skryté notifikace starší než 90 dní.',
                    'cron' => '0 3 * * *',
                    'is_active' => true,
                    'created_at' => date('Y-m-d H:i:s'),
                ],
            ])
            ->saveData();
    }

    public function down(): void
    {
        $this->execute(<<<SQL
            DELETE FROM scheduled_job WHERE class = 'App\\Scheduler\\PruneNotificationsJob';
            SQL);
    }
}
