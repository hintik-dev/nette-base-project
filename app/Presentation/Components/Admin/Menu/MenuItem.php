<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\Menu;

/**
 * Položka menu.
 *
 * Oprávnění se u položky **nezapisuje** — odvodí se z atributu
 * `#[RequiresPermission]` na cílovém presenteru, aby existovalo jen na jednom
 * místě. Viz MenuPermissionResolver.
 */
readonly class MenuItem
{
    /**
     * @param string|null $destination Cíl v plném tvaru (`:Admin:Page:default`).
     *                                 null = pouze rozbalovací skupina, jejíž
     *                                 viditelnost se řídí potomky.
     * @param string $activePattern Vzor pro zvýraznění aktivní položky.
     * @param list<MenuItem> $children
     */
    public function __construct(
        public string $label,
        public ?string $destination,
        public string $icon,
        public string $activePattern,
        public array $children = [],
    ) {
    }


    public function isGroup(): bool
    {
        return $this->destination === null;
    }
}
