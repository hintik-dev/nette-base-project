<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\ScheduledJob\ScheduledJobRunOutputGrid;

use App\Domain\ScheduledJob\ExplorerScheduledJobRunOutputRepository;
use App\Domain\ScheduledJob\ScheduledJobFacade;
use App\Presentation\Components\Base\BaseComponent;
use App\Presentation\Control\DataGrid\BaseGrid;

class ScheduledJobRunOutputGrid extends BaseComponent
{
    public function __construct(
        private readonly ScheduledJobFacade $facade,
        private readonly int $runId,
    ) {
    }


    public function createComponentGrid(): BaseGrid
    {
        $grid = new BaseGrid();

        $grid->setDataSource($this->facade->getOutputDataSourceForRun($this->runId));

        $grid->addColumnNumber(ExplorerScheduledJobRunOutputRepository::COLUMN_ID, '#')
            ->setFitContent();

        $grid->addColumnDateTime(ExplorerScheduledJobRunOutputRepository::COLUMN_CREATED_AT, 'Čas')
            ->setFitContent()
            ->setFormat('H:i:s.u');

        $grid->addColumnText(ExplorerScheduledJobRunOutputRepository::COLUMN_MESSAGE, 'Zpráva');

        return $grid;
    }
}
