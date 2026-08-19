<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateMailQueueTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('mail_queue', ['id' => true, 'primary_key' => 'id', 'comment' => 'Fronta e-mailů čekajících na asynchronní odeslání']);
        $table
            ->addColumn('recipient', 'string', ['limit' => 255, 'null' => false, 'comment' => 'E-mailová adresa příjemce'])
            ->addColumn('subject', 'string', ['limit' => 500, 'null' => false, 'comment' => 'Předmět e-mailu'])
            ->addColumn('body_html', 'text', ['null' => false, 'comment' => 'HTML obsah e-mailu (reálný, i citlivý — potřeba k odeslání)'])
            ->addColumn('mail_class', 'string', ['limit' => 255, 'null' => true, 'comment' => 'FQCN implementace Mail, ze které byla položka zařazena'])
            ->addColumn('is_sensitive', 'boolean', ['null' => false, 'default' => false, 'comment' => 'Zda mail obsahuje citlivá data — řídí redakci při archivaci do sent_email'])
            ->addColumn('priority', 'integer', ['null' => false, 'default' => 0, 'comment' => 'Vyšší hodnota = zpracováno dřív (transakční pošta > hromadná)'])
            ->addColumn('status', 'enum', [
                'values' => ['queued', 'processing'],
                'null' => false,
                'default' => 'queued',
                'comment' => 'Stav zpracování — terminální výsledek se archivuje do sent_email a řádek se maže',
            ])
            ->addColumn('attempts', 'integer', ['signed' => false, 'null' => false, 'default' => 0, 'comment' => 'Počet dosavadních pokusů o odeslání'])
            ->addColumn('max_attempts', 'integer', ['signed' => false, 'null' => false, 'default' => 5, 'comment' => 'Maximální počet pokusů před trvalým selháním'])
            ->addColumn('next_attempt_at', 'datetime', ['null' => true, 'comment' => 'Nejdřívější čas dalšího pokusu (retry backoff)'])
            ->addColumn('locked_at', 'datetime', ['null' => true, 'comment' => 'Čas, kdy položku nabral worker ke zpracování'])
            ->addColumn('locked_by', 'string', ['limit' => 100, 'null' => true, 'comment' => 'Identifikátor běhu/workeru, který položku zpracovává'])
            ->addColumn('error', 'text', ['null' => true, 'comment' => 'Poslední chybová zpráva (transportní, ne obsah e-mailu)'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP', 'comment' => 'Datum zařazení do fronty'])
            ->addIndex('status')
            ->addIndex('recipient')
            ->addIndex(['priority', 'id'])
            ->create();
    }
}
