<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePasswordResetTokenTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('password_reset_token', ['id' => true, 'primary_key' => 'id']);
        $table
            ->addColumn('user_id', 'integer', ['null' => false, 'comment' => 'FK App\\Domain\\User\\User::id'])
            ->addColumn('token_hash', 'string', ['limit' => 64, 'null' => false, 'comment' => 'SHA-256 hash surového tokenu z odkazu (surový token se do DB neukládá)'])
            ->addColumn('expires_at', 'datetime', ['null' => false, 'comment' => 'Konec platnosti odkazu'])
            ->addColumn('used_at', 'datetime', ['null' => true, 'default' => null, 'comment' => 'Čas spotřebování tokenu, null = dosud nepoužitý'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP', 'comment' => 'Čas vyžádání resetu'])
            ->addIndex('user_id')
            ->addIndex('token_hash', ['unique' => true])
            ->create();
    }
}
