<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUserSessionTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('user_session', ['id' => true, 'primary_key' => 'id']);
        $table
            ->addColumn('user_id', 'integer', ['null' => false, 'comment' => 'FK App\\Domain\\User\\User::id'])
            ->addColumn('ip_address', 'string', ['limit' => 45, 'null' => true, 'comment' => 'IP adresa při přihlášení'])
            ->addColumn('user_agent', 'text', ['null' => true, 'comment' => 'User-Agent hlavička při přihlášení'])
            ->addColumn('token', 'string', ['limit' => 64, 'null' => false, 'comment' => 'Token z AUTH_TOKEN cookie'])
            ->addColumn('expire_delta', 'integer', ['null' => true, 'default' => null, 'comment' => 'Inactivity timeout v sekundách, null = bez omezení (remember me)'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP', 'comment' => 'Čas přihlášení'])
            ->addColumn('last_activity_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP', 'comment' => 'Čas poslední aktivity (sliding expiration)'])
            ->addColumn('logged_out_at', 'datetime', ['null' => true, 'default' => null, 'comment' => 'Čas ukončení session'])
            ->addColumn('logout_reason', 'string', ['limit' => 50, 'null' => true, 'default' => null, 'comment' => 'manual/inactivity/forced/single_session, null = aktivní'])
            ->addIndex('user_id')
            ->addIndex('token', ['unique' => true])
            ->create();
    }
}
