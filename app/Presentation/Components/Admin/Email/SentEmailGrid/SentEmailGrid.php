<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\Email\SentEmailGrid;

use App\Domain\Email\ExplorerSentEmailRepository;
use App\Domain\Email\SentEmailFacade;
use App\Domain\Email\SentEmailStatus;
use App\Presentation\Components\Base\BaseGridComponent;
use App\Presentation\Control\DataGrid\BaseGrid;
use App\Presentation\Control\DataGrid\BaseGridFactory;
use Contributte\Datagrid\Column\Action\Confirmation\StringConfirmation;
use Nette\Utils\Html;

class SentEmailGrid extends BaseGridComponent
{
    public function __construct(
        private readonly SentEmailFacade $facade,
        BaseGridFactory $baseGridFactory,
    ) {
        parent::__construct($baseGridFactory);
    }


    protected function configureGrid(BaseGrid $grid): void
    {
        $grid->setDataSource($this->facade->getAllSelection());

        $grid->addColumnNumber(ExplorerSentEmailRepository::COLUMN_ID, 'ID')
            ->setFitContent();

        $grid->addColumnText(ExplorerSentEmailRepository::COLUMN_RECIPIENT, 'Příjemce')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText(ExplorerSentEmailRepository::COLUMN_SUBJECT, 'Předmět')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText(ExplorerSentEmailRepository::COLUMN_STATUS, 'Stav')
            ->setFitContent()
            ->setSortable()
            ->setRenderer(function ($row): Html {
                $status = SentEmailStatus::from($row[ExplorerSentEmailRepository::COLUMN_STATUS]);
                return match ($status) {
                    SentEmailStatus::Sent    => Html::el('span')->setAttribute('class', 'badge bg-success')->setText('Odesláno'),
                    SentEmailStatus::Failed  => Html::el('span')->setAttribute('class', 'badge bg-danger')->setText('Chyba'),
                    SentEmailStatus::Pending => Html::el('span')->setAttribute('class', 'badge bg-secondary')->setText('Čeká'),
                };
            });

        $grid->addFilterSelect(ExplorerSentEmailRepository::COLUMN_STATUS, 'Stav', [
            ''        => 'Vše',
            'sent'    => 'Odesláno',
            'failed'  => 'Chyba',
            'pending' => 'Čeká',
        ]);

        $grid->addColumnText(ExplorerSentEmailRepository::COLUMN_IS_SENSITIVE, 'Citlivý')
            ->setFitContent()
            ->setRenderer(function ($row): Html {
                return (bool) $row[ExplorerSentEmailRepository::COLUMN_IS_SENSITIVE]
                    ? Html::el('span')->setAttribute('class', 'badge bg-warning text-dark')->setText('Citlivý')
                    : Html::el('span');
            });

        $grid->addColumnDateTime(ExplorerSentEmailRepository::COLUMN_CREATED_AT, 'Vytvořeno')
            ->setSortable();

        $grid->addColumnDateTime(ExplorerSentEmailRepository::COLUMN_SENT_AT, 'Odesláno')
            ->setSortable();

        $grid->addActionCallback(
            'detail',
            'Zobrazit',
            function (string $id): void {
                $this->presenter->redirect('detail', ['id' => (int) $id]);
            },
        )->setIcon('eye')->setClass('btn btn-sm btn-outline-info');

        $grid->addActionCallback(
            'resend',
            'Odeslat znovu',
            function (string $id): void {
                $this->presenter->redirect('resend!', ['id' => (int) $id]);
            },
        )->setIcon('arrow-repeat')->setClass('btn btn-sm btn-outline-secondary')
         ->setConfirmation(new StringConfirmation('Opravdu chcete e-mail znovu odeslat?'));

        $grid->allowRowsAction(
            'resend',
            fn($item) => !(bool) $item[ExplorerSentEmailRepository::COLUMN_IS_SENSITIVE],
        );

        $grid->setDefaultSort([ExplorerSentEmailRepository::COLUMN_CREATED_AT => 'DESC']);
    }
}
