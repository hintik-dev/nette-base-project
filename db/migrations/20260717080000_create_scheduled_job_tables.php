<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateScheduledJobTables extends AbstractMigration
{
    public function up(): void
    {
        $jobs = $this->table('scheduled_job', ['id' => true, 'primary_key' => 'id']);
        $jobs
            ->addColumn('name', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('class', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('cron', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('is_active', 'boolean', ['null' => false, 'default' => true])
            ->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
            ->addIndex('class', ['unique' => true])
            ->create();

        $runs = $this->table('scheduled_job_run', ['id' => true, 'primary_key' => 'id']);
        $runs
            ->addColumn('scheduled_job_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('status', 'enum', [
                'values' => ['scheduled', 'running', 'completed', 'failed'],
                'null' => false,
                'default' => 'scheduled',
            ])
            ->addColumn('trigger', 'enum', [
                'values' => ['scheduler', 'manual'],
                'null' => false,
            ])
            ->addColumn('started_at', 'datetime', ['null' => false])
            ->addColumn('finished_at', 'datetime', ['null' => true])
            ->addColumn('duration_ms', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('error', 'text', ['null' => true])
            ->addIndex('scheduled_job_id')
            ->addForeignKey('scheduled_job_id', 'scheduled_job', 'id', ['delete' => 'CASCADE'])
            ->create();

        $output = $this->table('scheduled_job_run_output', ['id' => true, 'primary_key' => 'id']);
        $output
            ->addColumn('scheduled_job_run_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('message', 'text', ['null' => false])
            ->addIndex('scheduled_job_run_id')
            ->addForeignKey('scheduled_job_run_id', 'scheduled_job_run', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('scheduled_job')
            ->insert([
                [
                    'name' => 'Hello World',
                    'class' => 'App\\Scheduler\\HelloWorldJob',
                    'description' => 'Ukázková úloha – zapisuje zprávu do výstupu běhu.',
                    'cron' => '* * * * *',
                    'is_active' => false,
                    'created_at' => date('Y-m-d H:i:s'),
                ],
            ])
            ->saveData();
    }

    public function down(): void
    {
        $this->table('scheduled_job_run_output')->drop()->save();
        $this->table('scheduled_job_run')->drop()->save();
        $this->table('scheduled_job')->drop()->save();
    }
}
