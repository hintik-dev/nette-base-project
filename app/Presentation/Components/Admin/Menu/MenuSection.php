<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\Menu;

/**
 * Skupina položek pod společnou hlavičkou. Sekce bez viditelné položky
 * se nevykreslí — hlavička se tak nemusí hlídat ručně.
 */
readonly class MenuSection
{
    /** @param list<MenuItem> $items */
    public function __construct(
        public ?string $label,
        public array $items,
    ) {
    }
}
