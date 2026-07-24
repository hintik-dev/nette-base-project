<?php declare(strict_types=1);
namespace App\Domain\ValueStorage;

use App\Core\Database\ExplorerRepository;

class ExplorerValueStorageRepository extends ExplorerRepository
{
    public const string COLUMN_ID = 'id';
    public const string COLUMN_CATEGORY = 'category';
    public const string COLUMN_STORAGE_KEY = 'storage_key';
    public const string COLUMN_VALUE = 'value';

    public const string TABLE_NAME = 'value_storage';


    public function __construct(
        private readonly ExplorerValueStorageMapper $mapper,
    ) {
        parent::__construct(self::TABLE_NAME);
    }


    public function findByKey(string $category, string $key): ?ValueStorage
    {
        $row = $this->getTable()
            ->where(self::COLUMN_CATEGORY, $category)
            ->where(self::COLUMN_STORAGE_KEY, $key)
            ->fetch();

        if ($row === null) {
            return null;
        }

        return $this->mapper->mapValueStorage($row);
    }


    /**
     * @throws ValueStorageNotFoundException
     */
    public function getByKey(string $category, string $key): ValueStorage
    {
        $entry = $this->findByKey($category, $key);

        if ($entry === null) {
            throw new ValueStorageNotFoundException();
        }

        return $entry;
    }


    /**
     * @return ValueStorage[]
     */
    public function findByCategory(string $category): array
    {
        return array_map(
            fn($row) => $this->mapper->mapValueStorage($row),
            $this->getTable()
                ->where(self::COLUMN_CATEGORY, $category)
                ->fetchAll(),
        );
    }


    public function save(string $category, string $key, ?string $value): void
    {
        $this->saveMany($category, [$key => $value]);
    }


    /**
     * @param array<string, string|null> $values Mapa storage_key => value
     */
    public function saveMany(string $category, array $values): void
    {
        if (empty($values)) {
            return;
        }

        $placeholders = implode(', ', array_fill(0, count($values), '(?, ?, ?)'));
        $params = [];
        foreach ($values as $key => $value) {
            $params[] = $category;
            $params[] = $key;
            $params[] = $value;
        }

        $this->database->query(
            'INSERT INTO ' . self::TABLE_NAME . ' (category, storage_key, value) VALUES '
            . $placeholders . ' ON DUPLICATE KEY UPDATE value = VALUES(value)',
            ...$params,
        );
    }
}
