<?php declare(strict_types=1);
namespace App\Domain\ValueStorage;

use Nette\Database\Table\ActiveRow;

class ExplorerValueStorageMapper
{
    public function mapValueStorage(ActiveRow $row): ValueStorage
    {
        return new ValueStorage(
            id: $row[ExplorerValueStorageRepository::COLUMN_ID],
            category: $row[ExplorerValueStorageRepository::COLUMN_CATEGORY],
            key: $row[ExplorerValueStorageRepository::COLUMN_STORAGE_KEY],
            value: $row[ExplorerValueStorageRepository::COLUMN_VALUE],
        );
    }
}
