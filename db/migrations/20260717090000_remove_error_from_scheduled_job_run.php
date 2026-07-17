<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveErrorFromScheduledJobRun extends AbstractMigration
{
    public function up(): void
    {
        $this->table('scheduled_job_run')
            ->removeColumn('error')
            ->update();
    }

    public function down(): void
    {
        $this->table('scheduled_job_run')
            ->addColumn('error', 'text', ['null' => true])
            ->update();
    }
}
