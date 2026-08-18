<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Zavádí bypass ACL. Sloupec `role` zůstává, aby z něj mohla číst navazující
 * seed migrace; ta ho po převodu dat do user_x_user_role odstraní.
 */
final class AddIsSuperadminToUser extends AbstractMigration
{
    public function change(): void
    {
        $this->table('user')
            ->addColumn('is_superadmin', 'boolean', [
                'null' => false,
                'default' => false,
                'after' => 'password_hash',
                'comment' => 'Obchází ACL — vidí a smí vše. Pojistka proti zamčení se ven.',
            ])
            ->update();
    }
}
