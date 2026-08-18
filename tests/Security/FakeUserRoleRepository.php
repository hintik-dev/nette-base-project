<?php declare(strict_types=1);
namespace Tests\Security;

use App\Domain\UserRole\ExplorerUserRoleMapper;
use App\Domain\UserRole\ExplorerUserRoleRepository;

/**
 * Repozitář bez databáze — testuje se skládání vrstev, ne dotaz.
 * Řádky vrací ve stejném pořadí jako ostrý dotaz, tedy vzestupně podle priority.
 */
class FakeUserRoleRepository extends ExplorerUserRoleRepository
{
    public int $queryCount = 0;

    /**
     * @param list<array{priority: int, permission_key: string, effect: string}> $rows
     * @param list<array{priority: int, permission_key: string, effect: string}> $defaultRows
     */
    public function __construct(
        private readonly array $rows = [],
        private readonly array $defaultRows = [],
    ) {
        parent::__construct(new ExplorerUserRoleMapper());
    }


    /** @return list<array{priority: int, permission_key: string, effect: string}> */
    public function getEffectivePermissionRows(int $userId): array
    {
        $this->queryCount++;

        return $this->rows;
    }


    /** @return list<array{priority: int, permission_key: string, effect: string}> */
    public function getDefaultRolePermissionRows(): array
    {
        $this->queryCount++;

        return $this->defaultRows;
    }
}
