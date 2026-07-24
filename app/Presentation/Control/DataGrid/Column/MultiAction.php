<?php declare(strict_types=1);

namespace App\Presentation\Control\DataGrid\Column;

use Contributte\Datagrid\Datagrid;
use Nette\Utils\Html;

class MultiAction extends \Contributte\Datagrid\Column\MultiAction
{
    public function __construct(Datagrid $grid, string $key, string $name)
    {
        parent::__construct($grid, $key, $name);

        $this->setTemplate(__DIR__ . '/../templates/column_multi_action.latte');
    }

    public function renderButton(): Html
    {
        $button = parent::renderButton();

        $button->removeAttribute('data-toggle');
        $button->data('bs-toggle', 'dropdown');
        return $button;
    }
}
