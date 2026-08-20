<?php declare(strict_types=1);
namespace App\Presentation\Control\Form;

use App\Presentation\Control\Form\DynamicSelect\DynamicMultiSelect;
use App\Presentation\Control\Form\DynamicSelect\DynamicSelect;
use Nette\Application\UI\Form;

class BaseForm extends Form
{
    public function addCheckbox(string $name, string|\Stringable|null $caption = null): ICheckCheckbox
    {
        return $this[$name] = new ICheckCheckbox($caption);
    }


    /**
     * Přebíjí nativní addSelect() — DynamicSelect je vzhledově i funkčně
     * nadmnožina (vyhledávání, sjednocený vzhled s ostatními inputy), takže
     * dostane každý select v aplikaci bez nutnosti cokoli měnit na volajících.
     * @param ?mixed[] $items
     */
    public function addSelect(
        string $name,
        string|\Stringable|null $label = null,
        ?array $items = null,
        ?int $size = null,
    ): DynamicSelect {
        return $this[$name] = (new DynamicSelect($label, $items ?? []))
            ->setHtmlAttribute('size', $size > 1 ? $size : null);
    }


    /**
     * @see self::addSelect() — stejný důvod, stejné pravidlo.
     * @param ?mixed[] $items
     */
    public function addMultiSelect(
        string $name,
        string|\Stringable|null $label = null,
        ?array $items = null,
        ?int $size = null,
    ): DynamicMultiSelect {
        return $this[$name] = (new DynamicMultiSelect($label, $items ?? []))
            ->setHtmlAttribute('size', $size > 1 ? $size : null);
    }


    /**
     * Pro AJAX režim (viz DynamicSelect::setRemoteSource()) — na rozdíl od
     * addSelect() tu $items typicky obsahuje jen popisek předvyplněné
     * hodnoty, ne celou nabídku.
     * @param array<int|string, mixed> $items
     */
    public function addDynamicSelect(string $name, string|\Stringable|null $label = null, array $items = []): DynamicSelect
    {
        return $this[$name] = new DynamicSelect($label, $items);
    }


    /**
     * @see self::addDynamicSelect() — stejný důvod, stejné pravidlo.
     * @param array<int|string, mixed> $items
     */
    public function addDynamicMultiSelect(string $name, string|\Stringable|null $label = null, array $items = []): DynamicMultiSelect
    {
        return $this[$name] = new DynamicMultiSelect($label, $items);
    }
}
