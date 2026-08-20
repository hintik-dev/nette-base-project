<?php declare(strict_types=1);
namespace App\Presentation\Control\Form\DynamicSelect;

use Nette\Forms\Controls\MultiSelectBox;
use Stringable;

/**
 * Multiselect s vyhledáváním, vykreslený jako uzavřený dropdown
 * s odebiratelnými štítky (viz assets/admin/js/dynamic-select-init.js), ne
 * jako nativní listbox. Beze změny chování jde o drop-in náhradu za
 * addMultiSelect(); setRemoteSource() navíc přepne na AJAX dotazování.
 */
class DynamicMultiSelect extends MultiSelectBox
{
    private bool $remote = false;


    public function __construct(string|Stringable|null $label = null, array $items = [])
    {
        parent::__construct($label, $items);
        $this->setHtmlAttribute('data-dynamic-select', '1');
    }


    /**
     * @see DynamicSelect::setRemoteSource() — stejná pravidla i důvod.
     */
    public function setRemoteSource(string $endpointUrl, int $minChars = 2): static
    {
        $this->remote = true;
        $this->checkDefaultValue(false);
        $this->setHtmlAttribute('data-dynamic-select-source', $endpointUrl);
        $this->setHtmlAttribute('data-dynamic-select-min-chars', (string) $minChars);

        return $this;
    }


    /**
     * @see DynamicSelect::setPlaceholder() — stejná pravidla i důvod.
     */
    public function setPlaceholder(string $text): static
    {
        $this->setHtmlAttribute('data-dynamic-select-placeholder', $text);

        return $this;
    }


    /**
     * @see DynamicSelect::getValue() — stejná pravidla i důvod.
     * @return list<int|string>
     */
    public function getValue(): array
    {
        return $this->remote ? $this->getRawValue() : parent::getValue();
    }
}
