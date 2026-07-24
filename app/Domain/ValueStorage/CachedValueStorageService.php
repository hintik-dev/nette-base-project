<?php declare(strict_types=1);
namespace App\Domain\ValueStorage;

use Nette\Caching\Cache;
use Nette\Caching\Storage;

readonly class CachedValueStorageService
{
    private Cache $cache;

    public function __construct(
        private ValueStorageService $valueStorageService,
        Storage $cacheStorage,
    ) {
        $this->cache = new Cache($cacheStorage, 'value_storage');
    }


    public function get(string $category, string $key): ?string
    {
        return $this->cache->load(
            $this->cacheKey($category, $key),
            fn() => $this->valueStorageService->get($category, $key),
        );
    }


    public function getEntry(string $category, string $key): ?ValueStorage
    {
        return $this->cache->load(
            $this->cacheKey($category, $key) . '.entry',
            fn() => $this->valueStorageService->getEntry($category, $key),
        );
    }


    /**
     * @return array<string, string|null>
     */
    public function getCategory(string $category): array
    {
        return $this->cache->load(
            'category.' . $category,
            fn() => $this->valueStorageService->getCategory($category),
        );
    }


    public function set(string $category, string $key, ?string $value): void
    {
        $this->valueStorageService->set($category, $key, $value);

        $this->cache->remove($this->cacheKey($category, $key));
        $this->cache->remove($this->cacheKey($category, $key) . '.entry');
        $this->cache->remove('category.' . $category);
    }


    private function cacheKey(string $category, string $key): string
    {
        return $category . '.' . $key;
    }
}
