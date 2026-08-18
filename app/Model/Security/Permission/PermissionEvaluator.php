<?php declare(strict_types=1);
namespace App\Model\Security\Permission;

use App\Domain\UserRole\ExplorerUserRoleRepository;
use App\Domain\UserRole\PermissionEffect;

/**
 * Skládá efektivní oprávnění uživatele z jeho rolí.
 *
 * Vrstvy se aplikují vzestupně podle priority role — explicitní záznam ve vyšší
 * prioritě přepíše nižší, neutrál (absence záznamu) nepřepisuje nic. Klíč, pro
 * který nakonec nikdo nic neřekl, je zakázaný (default deny).
 *
 * Výchozí role (priorita 0) je vždy součástí sady, i pro uživatele bez rolí
 * a pro nepřihlášeného návštěvníka.
 *
 * Cache je pouze v rámci requestu. Nic se neukládá do session ani do perzistentní
 * cache, aby se odebrání práva projevilo hned u dalšího requestu.
 *
 * Registruje se ručně v config/services.neon.
 */
final class PermissionEvaluator
{
    /**
     * userId (0 = nepřihlášený) => klíč oprávnění => povoleno
     *
     * @var array<int, array<string, bool>>
     */
    private array $cache = [];


    public function __construct(
        private readonly ExplorerUserRoleRepository $userRoleRepository,
    ) {
    }


    public function isAllowed(?int $userId, string $permissionKey): bool
    {
        return $this->getEffectivePermissions($userId)[$permissionKey] ?? false;
    }


    /**
     * Efektivní sada oprávnění. Vrací jen klíče, o kterých někdo rozhodl;
     * chybějící klíč znamená zákaz.
     *
     * @return array<string, bool>
     */
    public function getEffectivePermissions(?int $userId): array
    {
        $cacheKey = $userId ?? 0;

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $rows = $userId === null
            ? $this->userRoleRepository->getDefaultRolePermissionRows()
            : $this->userRoleRepository->getEffectivePermissionRows($userId);

        $effective = [];

        // Řádky přicházejí seřazené vzestupně podle priority role, takže
        // pozdější zápis je ten z vyšší priority a má přepsat dřívější.
        foreach ($rows as $row) {
            $effective[$row['permission_key']] = $row['effect'] === PermissionEffect::Allow->value;
        }

        return $this->cache[$cacheKey] = $effective;
    }


    /**
     * Zahodí cache — volá se po změně rolí nebo oprávnění, aby se zásah
     * projevil ještě v probíhajícím requestu.
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }
}
