<?php declare(strict_types=1);
namespace App\Presentation\Accessory;

use App\Model\Security\Permission\PermissionDefinition;
use App\Model\Security\SecurityUser;
use Latte\Extension;

final class LatteExtension extends Extension
{
    /**
     * SecurityUser je volitelný jen kvůli skriptu latte-lint, který extension
     * vytváří mimo DI kontejner a funkce nevyhodnocuje — pouze ověřuje, že
     * existují. Za běhu ho autowiring dodá vždy.
     */
    public function __construct(
        private readonly ?SecurityUser $securityUser = null,
    ) {
    }


    public function getFilters(): array
    {
        return [];
    }


    /**
     * `isAllowed()` v šabloně umožní schovat prvek, na který uživatel nemá
     * právo — cílem je, aby se nic nezobrazovalo podle role, ale podle
     * konkrétního oprávnění.
     *
     * Je to jen UX: skryté tlačítko není ochrana. Skutečné vynucení dělá
     * atribut RequiresPermission na presenteru a kontrola ve fasádě.
     */
    public function getFunctions(): array
    {
        return [
            'isAllowed' => fn (PermissionDefinition $permission): bool
                => $this->securityUser?->isAllowed($permission) ?? false,
        ];
    }
}
