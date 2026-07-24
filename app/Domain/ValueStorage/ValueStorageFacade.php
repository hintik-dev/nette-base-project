<?php declare(strict_types=1);
namespace App\Domain\ValueStorage;

use App\Model\Security\Authorizator\InsufficientPrivilegesException;
use App\Model\Security\SecurityUser;

class ValueStorageFacade
{
    public function __construct(
        private readonly ValueStorageService $valueStorageService,
        private readonly CachedValueStorageService $cachedValueStorageService,
        private readonly SecurityUser $securityUser,
    ) {
    }


    public function get(string $category, string $key, bool $cached = false): ?string
    {
        if ($cached) {
            return $this->cachedValueStorageService->get($category, $key);
        }

        return $this->valueStorageService->get($category, $key);
    }


    public function getEntry(string $category, string $key, bool $cached = false): ?ValueStorage
    {
        if ($cached) {
            return $this->cachedValueStorageService->getEntry($category, $key);
        }

        return $this->valueStorageService->getEntry($category, $key);
    }


    /**
     * @return array<string, string|null>
     */
    public function getCategory(string $category, bool $cached = false): array
    {
        if ($cached) {
            return $this->cachedValueStorageService->getCategory($category);
        }

        return $this->valueStorageService->getCategory($category);
    }


    /**
     * @throws InsufficientPrivilegesException
     */
    public function set(string $category, string $key, ?string $value): void
    {
        if (!$this->securityUser->isAllowed('value-storage', 'edit')) {
            throw new InsufficientPrivilegesException();
        }

        // Zápis přes cached service, aby se automaticky invalidovala cache
        $this->cachedValueStorageService->set($category, $key, $value);
    }
}
