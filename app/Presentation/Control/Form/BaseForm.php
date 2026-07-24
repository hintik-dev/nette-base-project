<?php declare(strict_types=1);
namespace App\Presentation\Control\Form;

use Nette\Application\UI\Form;

class BaseForm extends Form
{
    public function addCheckbox(string $name, string|\Stringable|null $caption = null): ICheckCheckbox
    {
        return $this[$name] = new ICheckCheckbox($caption);
    }
}
