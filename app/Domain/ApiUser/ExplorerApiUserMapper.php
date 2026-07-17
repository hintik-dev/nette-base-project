<?php declare(strict_types=1);
namespace App\Domain\ApiUser;

use Nette\Database\Table\ActiveRow;

class ExplorerApiUserMapper
{
    public function mapApiUser(ActiveRow $row): ApiUser
    {
        return new ApiUser(
            id: $row[ExplorerApiUserRepository::COLUMN_ID],
            name: $row[ExplorerApiUserRepository::COLUMN_NAME],
            token: $row[ExplorerApiUserRepository::COLUMN_TOKEN],
            description: $row[ExplorerApiUserRepository::COLUMN_DESCRIPTION],
            isActive: (bool) $row[ExplorerApiUserRepository::COLUMN_IS_ACTIVE],
            validFrom: $row[ExplorerApiUserRepository::COLUMN_VALID_FROM],
            validTo: $row[ExplorerApiUserRepository::COLUMN_VALID_TO],
            createdAt: $row[ExplorerApiUserRepository::COLUMN_CREATED_AT],
        );
    }
}
