<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\ScheduledJob\ScheduledJobGrid;

use App\Domain\ScheduledJob\ExplorerScheduledJobRepository;
use App\Domain\ScheduledJob\ScheduledJobFacade;
use App\Presentation\Components\Base\BaseGridComponent;
use App\Presentation\Control\DataGrid\BaseGrid;
use App\Presentation\Control\DataGrid\BaseGridFactory;
use Cron\CronExpression;
use Nette\Utils\Html;

class ScheduledJobGrid extends BaseGridComponent
{
    public function __construct(
        private readonly ScheduledJobFacade $facade,
        BaseGridFactory $baseGridFactory,
    ) {
        parent::__construct($baseGridFactory);
    }


    protected function configureGrid(BaseGrid $grid): void
    {
        $grid->setDataSource($this->facade->getAllDataSource());

        $grid->addColumnNumber(ExplorerScheduledJobRepository::COLUMN_ID, 'ID')
            ->setFitContent();

        $grid->addColumnText(ExplorerScheduledJobRepository::COLUMN_NAME, 'Název');

        $grid->addColumnText(ExplorerScheduledJobRepository::COLUMN_CLASS, 'Třída')
            ->setRenderer(function ($row): string {
                $parts = explode('\\', $row[ExplorerScheduledJobRepository::COLUMN_CLASS]);
                return end($parts);
            });

        $grid->addColumnText(ExplorerScheduledJobRepository::COLUMN_CRON, 'Cron');

        $grid->addColumnText(ExplorerScheduledJobRepository::COLUMN_IS_ACTIVE, 'Stav')
            ->setFitContent()
            ->setRenderer(function ($row): Html {
                $active = (bool) $row[ExplorerScheduledJobRepository::COLUMN_IS_ACTIVE];
                $badge = Html::el('span')
                    ->setAttribute('class', $active ? 'badge bg-success' : 'badge bg-secondary');
                $badge->setText($active ? 'Aktivní' : 'Neaktivní');
                return $badge;
            });

        $grid->addColumnText('last_run_at', 'Poslední běh')
            ->setRenderer(function ($row): string {
                $lastRunAt = $this->facade->getLastRunAt((int) $row[ExplorerScheduledJobRepository::COLUMN_ID]);
                return $lastRunAt !== null ? $lastRunAt->format('d.m.Y H:i:s') : '—';
            });

        $grid->addColumnText('next_run_at', 'Příští běh')
            ->setRenderer(function ($row): string {
                $expression = new CronExpression($row[ExplorerScheduledJobRepository::COLUMN_CRON]);
                $next = \DateTimeImmutable::createFromMutable($expression->getNextRunDate());
                return $next->format('d.m.Y H:i:s');
            });

        $grid->addActionCallback(
            'deactivate',
            'Deaktivovat',
            function (string $id): void {
                $this->presenter->redirect('setActive!', ['id' => (int) $id, 'active' => false]);
            },
        )->setIcon('power')->setClass('btn btn-sm btn-outline-secondary')
            ->setRenderCondition(fn($row) => (bool) $row[ExplorerScheduledJobRepository::COLUMN_IS_ACTIVE]);

        $grid->addActionCallback(
            'activate',
            'Aktivovat',
            function (string $id): void {
                $this->presenter->redirect('setActive!', ['id' => (int) $id, 'active' => true]);
            },
        )->setIcon('power')->setClass('btn btn-sm btn-outline-secondary')
            ->setRenderCondition(fn($row) => !$row[ExplorerScheduledJobRepository::COLUMN_IS_ACTIVE]);

        $grid->addActionCallback(
            'runNow',
            'Spustit hned',
            function (string $id): void {
                $this->presenter->redirect('runNow!', ['id' => (int) $id]);
            },
        )->setIcon('play-fill')->setClass('btn btn-sm btn-outline-success');

        $grid->addActionCallback(
            'runs',
            'Zobrazit běhy',
            function (string $id): void {
                $this->presenter->redirect('runs', ['jobId' => (int) $id]);
            },
        )->setIcon('journal-text')->setClass('btn btn-sm btn-outline-info');
    }
}
