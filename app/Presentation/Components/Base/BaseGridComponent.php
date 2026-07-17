<?php declare(strict_types=1);

namespace App\Presentation\Components\Base;

use App\Presentation\Control\DataGrid\BaseGrid;
use App\Presentation\Control\DataGrid\BaseGridFactory;

/**
 * Základ pro komponenty obalující datagrid (*Grid, *ListGrid).
 * Řeší společné věci pro všechny gridy v aplikaci (aktuálně vytvoření
 * BaseGrid instance s nastaveným translatorem) – potomek jen nastaví
 * sloupce, filtry a akce v configureGrid().
 */
abstract class BaseGridComponent extends BaseComponent
{
    public function __construct(
        private readonly BaseGridFactory $baseGridFactory,
    ) {
    }

    final public function createComponentGrid(): BaseGrid
    {
        $grid = $this->baseGridFactory->create();
        $this->configureGrid($grid);

        return $grid;
    }

    abstract protected function configureGrid(BaseGrid $grid): void;
}
