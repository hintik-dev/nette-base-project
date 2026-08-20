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
     * Drop-in náhrada za addSelect() s vyhledáváním (viz DynamicSelect).
     * @param array<int|string, mixed> $items
     */
    public function addDynamicSelect(string $name, string|\Stringable|null $label = null, array $items = []): DynamicSelect
    {
        return $this[$name] = new DynamicSelect($label, $items);
    }


    /**
     * Drop-in náhrada za addMultiSelect() s vyhledáváním (viz DynamicMultiSelect).
     * @param array<int|string, mixed> $items
     */
    public function addDynamicMultiSelect(string $name, string|\Stringable|null $label = null, array $items = []): DynamicMultiSelect
    {
        return $this[$name] = new DynamicMultiSelect($label, $items);
    }
}
