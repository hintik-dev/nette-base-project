<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\ScheduledJob\ScheduledJobRunOutputGrid;

use App\Domain\ScheduledJob\ExplorerScheduledJobRunOutputRepository;
use App\Domain\ScheduledJob\ScheduledJobFacade;
use App\Presentation\Components\Base\BaseGridComponent;
use App\Presentation\Control\DataGrid\BaseGrid;
use App\Presentation\Control\DataGrid\BaseGridFactory;

class ScheduledJobRunOutputGrid extends BaseGridComponent
{
    public function __construct(
        private readonly ScheduledJobFacade $facade,
        private readonly int $runId,
        BaseGridFactory $baseGridFactory,
    ) {
        parent::__construct($baseGridFactory);
    }


    protected function configureGrid(BaseGrid $grid): void
    {
        $grid->setDataSource($this->facade->getOutputDataSourceForRun($this->runId));

        $grid->addColumnNumber(ExplorerScheduledJobRunOutputRepository::COLUMN_ID, '#')
            ->setFitContent();

        $grid->addColumnDateTime(ExplorerScheduledJobRunOutputRepository::COLUMN_CREATED_AT, 'Čas')
            ->setFitContent()
            ->setFormat('H:i:s.u');

        $grid->addColumnText(ExplorerScheduledJobRunOutputRepository::COLUMN_MESSAGE, 'Zpráva');
    }
}
