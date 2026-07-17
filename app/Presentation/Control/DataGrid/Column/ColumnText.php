<?php declare(strict_types=1);

namespace App\Presentation\Control\DataGrid\Column;

use Contributte\Datagrid\Row;
use Nette\Utils\Html;

class ColumnText extends \Contributte\Datagrid\Column\ColumnText
{
    /** @var callable|null */
    private $copyValueCallback = null;

    public function setCopyValue(callable $callback): static
    {
        $this->copyValueCallback = $callback;
        return $this;
    }

    public function render(Row $row): mixed
    {
        $value = parent::render($row);

        if ($this->copyValueCallback === null) {
            return $value;
        }

        $copyText = ($this->copyValueCallback)($row->getItem());

        $btn = Html::el('button')
            ->type('button')
            ->class('btn btn-outline-secondary btn-xs py-0 px-1')
            ->title($copyText)
            ->setAttribute('onclick', sprintf(
                "var b=this;navigator.clipboard.writeText(%s).then(function(){b.innerHTML='<i class=\"bi bi-check\"></i>"
                    . "';setTimeout(function(){b.innerHTML='<i class=\"bi bi-clipboard\"></i>';},1500)}).catch(function(){})",
                json_encode($copyText),
            ))
            ->addHtml(Html::el('i')->class('bi bi-clipboard'));

        $text = Html::el('span')->style('flex:1');

        if ($value instanceof Html) {
            $text->addHtml($value);
        } else {
            $text->addText((string) $value);
        }

        return Html::el('span')->style('display:flex;align-items:center;gap:6px')
            ->addHtml($text)
            ->addHtml($btn);
    }
}
