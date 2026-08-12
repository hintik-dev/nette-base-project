<?php declare(strict_types=1);
namespace App\Domain\User;

use Nette\Database\Table\ActiveRow;

class ExplorerUserMapper
{
    public function mapUser(ActiveRow $row): User
    {
        return new User(
            id: $row[ExplorerUserRepository::COLUMN_ID],
            email: $row[ExplorerUserRepository::COLUMN_EMAIL],
            passwordHash: $row[ExplorerUserRepository::COLUMN_PASSWORD_HASH],
            isSuperadmin: (bool) $row[ExplorerUserRepository::COLUMN_IS_SUPERADMIN],
            active: (bool) $row[ExplorerUserRepository::COLUMN_ACTIVE],
            lastLogin: $row[ExplorerUserRepository::COLUMN_LAST_LOGIN] ?? null,
        );
    }
}
