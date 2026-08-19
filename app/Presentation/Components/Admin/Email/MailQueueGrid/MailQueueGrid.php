<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\Email\MailQueueGrid;

use App\Domain\Email\ExplorerMailQueueRepository;
use App\Domain\Email\MailQueueStatus;
use App\Presentation\Components\Base\BaseGridComponent;
use App\Presentation\Control\DataGrid\BaseGrid;
use App\Presentation\Control\DataGrid\BaseGridFactory;
use Nette\Utils\Html;

class MailQueueGrid extends BaseGridComponent
{
    public function __construct(
        private readonly ExplorerMailQueueRepository $repository,
        BaseGridFactory $baseGridFactory,
    ) {
        parent::__construct($baseGridFactory);
    }


    protected function configureGrid(BaseGrid $grid): void
    {
        $grid->setDataSource($this->repository->getAllSelection());

        $grid->addColumnNumber(ExplorerMailQueueRepository::COLUMN_ID, 'ID')
            ->setFitContent();

        $grid->addColumnText(ExplorerMailQueueRepository::COLUMN_RECIPIENT, 'Příjemce')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText(ExplorerMailQueueRepository::COLUMN_SUBJECT, 'Předmět')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText(ExplorerMailQueueRepository::COLUMN_STATUS, 'Stav')
            ->setFitContent()
            ->setSortable()
            ->setRenderer(function ($row): Html {
                $status = MailQueueStatus::from($row[ExplorerMailQueueRepository::COLUMN_STATUS]);
                return match ($status) {
                    MailQueueStatus::Processing => Html::el('span')->setAttribute('class', 'badge bg-info')->setText('Zpracovává se'),
                    MailQueueStatus::Queued      => Html::el('span')->setAttribute('class', 'badge bg-secondary')->setText('Čeká'),
                };
            });

        $grid->addColumnText(ExplorerMailQueueRepository::COLUMN_ATTEMPTS, 'Pokusy')
            ->setFitContent()
            ->setRenderer(fn($row) => sprintf(
                '%d / %d',
                $row[ExplorerMailQueueRepository::COLUMN_ATTEMPTS],
                $row[ExplorerMailQueueRepository::COLUMN_MAX_ATTEMPTS],
            ));

        $grid->addColumnNumber(ExplorerMailQueueRepository::COLUMN_PRIORITY, 'Priorita')
            ->setFitContent()
            ->setSortable();

        $grid->addColumnText(ExplorerMailQueueRepository::COLUMN_IS_SENSITIVE, 'Citlivý')
            ->setFitContent()
            ->setRenderer(function ($row): Html {
                return (bool) $row[ExplorerMailQueueRepository::COLUMN_IS_SENSITIVE]
                    ? Html::el('span')->setAttribute('class', 'badge bg-warning text-dark')->setText('Citlivý')
                    : Html::el('span');
            });

        $grid->addColumnDateTime(ExplorerMailQueueRepository::COLUMN_NEXT_ATTEMPT_AT, 'Další pokus')
            ->setSortable();

        $grid->addColumnDateTime(ExplorerMailQueueRepository::COLUMN_CREATED_AT, 'Zařazeno')
            ->setSortable();

        $grid->setDefaultSort([
            ExplorerMailQueueRepository::COLUMN_PRIORITY => 'DESC',
            ExplorerMailQueueRepository::COLUMN_ID        => 'ASC',
        ]);
    }
}
