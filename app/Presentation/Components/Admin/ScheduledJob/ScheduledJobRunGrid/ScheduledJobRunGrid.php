<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\ScheduledJob\ScheduledJobRunGrid;

use App\Domain\ScheduledJob\ExplorerScheduledJobRepository;
use App\Domain\ScheduledJob\ExplorerScheduledJobRunRepository;
use App\Domain\ScheduledJob\ScheduledJobFacade;
use App\Domain\ScheduledJob\ScheduledJobRunStatus;
use App\Domain\ScheduledJob\ScheduledJobRunTrigger;
use App\Presentation\Components\Base\BaseComponent;
use App\Presentation\Control\DataGrid\BaseGrid;
use Contributte\Translation\Translator;
use Nette\Utils\Html;

class ScheduledJobRunGrid extends BaseComponent
{
    public function __construct(
        private readonly ScheduledJobFacade $facade,
        private readonly Translator $translator,
        private readonly ?int $scheduledJobId,
    ) {
    }


    public function createComponentGrid(): BaseGrid
    {
        $grid = new BaseGrid();

        $dataSource = $this->scheduledJobId !== null
            ? $this->facade->getRunsDataSourceForJob($this->scheduledJobId)
            : $this->facade->getAllRunsDataSource();

        $grid->setDataSource($dataSource);

        $grid->addColumnNumber(ExplorerScheduledJobRunRepository::COLUMN_ID, 'ID')
            ->setFitContent();

        if ($this->scheduledJobId === null) {
            $grid->addColumnText(ExplorerScheduledJobRunRepository::COLUMN_SCHEDULED_JOB_ID, 'Úloha')
                ->setRenderer(function ($row): string {
                    $jobRow = $row->ref('scheduled_job', ExplorerScheduledJobRunRepository::COLUMN_SCHEDULED_JOB_ID);
                    return $jobRow !== null ? (string) $jobRow[ExplorerScheduledJobRepository::COLUMN_NAME] : '?';
                });
        }

        $grid->addColumnText(ExplorerScheduledJobRunRepository::COLUMN_STATUS, 'Stav')
            ->setFitContent()
            ->setRenderer(function ($row): Html {
                $status = ScheduledJobRunStatus::from($row[ExplorerScheduledJobRunRepository::COLUMN_STATUS]);
                return Html::el('span')
                    ->setAttribute('class', $status->toBadgeClass())
                    ->setText($status->toLabel());
            });

        $grid->addColumnText(ExplorerScheduledJobRunRepository::COLUMN_TRIGGER, 'Spuštění')
            ->setFitContent()
            ->setRenderer(function ($row): string {
                return ScheduledJobRunTrigger::from($row[ExplorerScheduledJobRunRepository::COLUMN_TRIGGER])->toLabel();
            });

        $grid->addColumnDateTime(ExplorerScheduledJobRunRepository::COLUMN_STARTED_AT, 'Zahájeno')
            ->setFormat('d.m.Y H:i:s');

        $grid->addColumnDateTime(ExplorerScheduledJobRunRepository::COLUMN_FINISHED_AT, 'Dokončeno')
            ->setFormat('d.m.Y H:i:s');

        $grid->addColumnText(ExplorerScheduledJobRunRepository::COLUMN_DURATION_MS, 'Trvání')
            ->setFitContent()
            ->setRenderer(function ($row): string {
                $ms = $row[ExplorerScheduledJobRunRepository::COLUMN_DURATION_MS];
                if ($ms === null) {
                    return '—';
                }
                return $ms >= 1000
                    ? round($ms / 1000, 2) . ' s'
                    : $ms . ' ms';
            });

        $grid->addActionCallback(
            'detail',
            'Detail',
            function (string $id): void {
                $this->presenter->redirect('runDetail', ['id' => (int) $id]);
            },
        )->setIcon('eye-fill')->setClass('btn btn-sm btn-outline-secondary');

        $grid->setDefaultSort([ExplorerScheduledJobRunRepository::COLUMN_STARTED_AT => 'DESC']);

        $grid->setTranslator($this->translator);

        return $grid;
    }
}
