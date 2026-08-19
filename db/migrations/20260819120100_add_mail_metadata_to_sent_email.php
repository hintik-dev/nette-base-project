<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddMailMetadataToSentEmail extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('sent_email');
        $table
            ->addColumn('mail_class', 'string', ['limit' => 255, 'null' => true, 'after' => 'body_html', 'comment' => 'FQCN implementace Mail, ze které byl e-mail odeslán'])
            ->addColumn('is_sensitive', 'boolean', ['null' => false, 'default' => false, 'after' => 'mail_class', 'comment' => 'Zda e-mail obsahoval citlivá data — pro takové je body_html vždy null a opětovné odeslání je zakázáno'])
            ->update();
    }
}
