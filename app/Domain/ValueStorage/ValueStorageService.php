<?php declare(strict_types=1);
namespace App\Domain\ValueStorage;

readonly class ValueStorageService
{
    public function __construct(
        private ExplorerValueStorageRepository $valueStorageRepository,
    ) {
    }


    public function get(string $category, string $key): ?string
    {
        return $this->valueStorageRepository->findByKey($category, $key)?->value;
    }


    public function getEntry(string $category, string $key): ?ValueStorage
    {
        return $this->valueStorageRepository->findByKey($category, $key);
    }


    /**
     * @return array<string, string|null>
     */
    public function getCategory(string $category): array
    {
        $entries = $this->valueStorageRepository->findByCategory($category);

        $result = [];
        foreach ($entries as $entry) {
            $result[$entry->key] = $entry->value;
        }

        return $result;
    }


    public function set(string $category, string $key, ?string $value): void
    {
        $this->valueStorageRepository->save($category, $key, $value);
    }


    /**
     * @param array<string, string|null> $values Mapa key => value
     */
    public function setMany(string $category, array $values): void
    {
        $this->valueStorageRepository->saveMany($category, $values);
    }
}
