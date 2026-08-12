<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Autor stránky — bez něj nelze vyhodnotit vlastnická oprávnění (page.edit.own).
 *
 * Sloupec je nullable: existující stránky autora nemají a smazání uživatele
 * ho vynuluje místo toho, aby vzalo stránku s sebou. Stránka bez autora
 * nepatří nikomu, takže vlastnické oprávnění na ni nikdy nezabere.
 */
final class AddAuthorToPage extends AbstractMigration
{
    public function change(): void
    {
        $this->table('page')
            ->addColumn('author_id', 'integer', [
                'null' => true,
                'signed' => false,
                'after' => 'title',
                'comment' => 'FK App\\Domain\\User\\User::id — autor stránky',
            ])
            ->addIndex('author_id')
            ->addForeignKey('author_id', 'user', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
            ->update();
    }
}
