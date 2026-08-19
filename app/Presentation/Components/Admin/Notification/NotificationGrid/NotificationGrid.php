<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\Notification\NotificationGrid;

use App\Domain\Notification\ExplorerNotificationRecipientRepository;
use App\Domain\Notification\NotificationFacade;
use App\Domain\Notification\NotificationType;
use App\Presentation\Components\Base\BaseGridComponent;
use App\Presentation\Control\DataGrid\BaseGrid;
use App\Presentation\Control\DataGrid\BaseGridFactory;
use Contributte\Datagrid\Column\Action\Confirmation\StringConfirmation;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\Html;
use Nette\Utils\Strings;

class NotificationGrid extends BaseGridComponent
{
    public function __construct(
        private readonly NotificationFacade $facade,
        BaseGridFactory $baseGridFactory,
    ) {
        parent::__construct($baseGridFactory);
    }


    protected function configureGrid(BaseGrid $grid): void
    {
        $grid->setDataSource($this->facade->getAllSelection());

        $grid->addColumnText(ExplorerNotificationRecipientRepository::COLUMN_TYPE, 'Typ')
            ->setFitContent()
            ->setRenderer(function (ActiveRow $row): Html {
                $type = NotificationType::from($row[ExplorerNotificationRecipientRepository::COLUMN_TYPE]);
                return match ($type) {
                    NotificationType::Security => Html::el('span')->setAttribute('class', 'badge bg-danger')->setText('Zabezpečení'),
                    NotificationType::System   => Html::el('span')->setAttribute('class', 'badge bg-primary')->setText('Systém'),
                    NotificationType::Warning  => Html::el('span')
                        ->setAttribute('class', 'badge bg-warning text-dark')
                        ->setText('Upozornění'),
                    NotificationType::Success  => Html::el('span')->setAttribute('class', 'badge bg-success')->setText('Úspěch'),
                    NotificationType::Info     => Html::el('span')->setAttribute('class', 'badge bg-secondary')->setText('Info'),
                };
            });

        $grid->addColumnText(ExplorerNotificationRecipientRepository::COLUMN_TITLE, 'Nadpis')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText(ExplorerNotificationRecipientRepository::COLUMN_MESSAGE, 'Zpráva')
            ->setRenderer(fn(ActiveRow $row) => Strings::truncate($row[ExplorerNotificationRecipientRepository::COLUMN_MESSAGE], 80));

        $grid->addColumnDateTime(ExplorerNotificationRecipientRepository::COLUMN_NOTIFICATION_CREATED_AT, 'Vytvořeno')
            ->setSortable();

        $grid->addColumnText(ExplorerNotificationRecipientRepository::COLUMN_READ_AT, 'Stav')
            ->setFitContent()
            ->setRenderer(function (ActiveRow $row): Html {
                return $row[ExplorerNotificationRecipientRepository::COLUMN_READ_AT] !== null
                    ? Html::el('span')->setAttribute('class', 'badge bg-secondary')->setText('Přečteno')
                    : Html::el('span')->setAttribute('class', 'badge bg-info')->setText('Nepřečteno');
            });

        $grid->addActionCallback(
            'markAsRead',
            'Označit přečtené',
            function (string $id): void {
                $this->presenter->redirect('markAsRead!', ['id' => (int) $id]);
            },
        )->setIcon('check2')->setClass('btn btn-sm btn-outline-secondary');

        $grid->allowRowsAction(
            'markAsRead',
            fn(ActiveRow $item) => $item[ExplorerNotificationRecipientRepository::COLUMN_READ_AT] === null,
        );

        $grid->addActionCallback(
            'hide',
            'Skrýt',
            function (string $id): void {
                $this->presenter->redirect('hide!', ['id' => (int) $id]);
            },
        )->setIcon('x-lg')->setClass('btn btn-sm btn-outline-danger')
         ->setConfirmation(new StringConfirmation('Opravdu chcete notifikaci skrýt?'));

        $grid->setDefaultSort([ExplorerNotificationRecipientRepository::COLUMN_NOTIFICATION_CREATED_AT => 'DESC']);
    }
}
