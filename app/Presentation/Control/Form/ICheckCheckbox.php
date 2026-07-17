<?php declare(strict_types=1);
namespace App\Presentation\Control\Form;

use Nette\Forms\Controls\Checkbox;
use Nette\Utils\Html;

class ICheckCheckbox extends Checkbox
{
    public function getControl(): Html
    {
        return Html::el('div')
            ->class('icheck-primary')
            ->addHtml($this->getControlPart())
            ->addHtml($this->getLabelPart());
    }
}
