<?php declare(strict_types=1);
namespace App\Presentation\Control\Form\DynamicSelect;

use Nette\Forms\Controls\SelectBox;
use Stringable;

/**
 * Select s vyhledáváním (viz assets/admin/js/dynamic-select-init.js). Beze
 * změny chování jde o drop-in náhradu za addSelect(); setRemoteSource()
 * navíc přepne na AJAX dotazování (viz DynamicSelectPresenter).
 */
class DynamicSelect extends SelectBox
{
    private bool $remote = false;


    public function __construct(string|Stringable|null $label = null, array $items = [])
    {
        parent::__construct($label, $items);
        $this->setHtmlAttribute('data-dynamic-select', '1');
    }


    /**
     * Přepne control do AJAX režimu — $items musí obsahovat jen popisky
     * aktuálně vybrané hodnoty (viz DynamicSelectSource::resolveLabels()),
     * zbytek dotahuje JS z $endpointUrl. checkDefaultValue(false) je nutné,
     * protože zbytek nabídky se nikdy nevykreslí do stránky.
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
     * V remote režimu items obsahuje jen předvyplněnou hodnotu — zděděné
     * getValue() by cokoli jiného odeslaného tiše zahodilo (hodnota není
     * v $items). Vrací se tak nevalidovaná odeslaná hodnota; přijímající
     * doménová vrstva ji musí ověřit sama (stejně jako dnes
     * UserRoleService::setRolesForUser() filtruje proti getAssignable()).
     */
    public function getValue(): mixed
    {
        return $this->remote ? $this->getRawValue() : parent::getValue();
    }
}
