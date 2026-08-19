<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\Notification\SystemNotificationGrid;

use App\Domain\Notification\ExplorerNotificationRepository;
use App\Domain\Notification\NotificationFacade;
use App\Domain\Notification\NotificationType;
use App\Presentation\Components\Base\BaseGridComponent;
use App\Presentation\Control\DataGrid\BaseGrid;
use App\Presentation\Control\DataGrid\BaseGridFactory;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\Html;
use Nette\Utils\Strings;

/** Přehled všech notifikací v systému napříč uživateli — read-only, viz NotificationFacade::getSystemSelection(). */
class SystemNotificationGrid extends BaseGridComponent
{
    public function __construct(
        private readonly NotificationFacade $facade,
        BaseGridFactory $baseGridFactory,
    ) {
        parent::__construct($baseGridFactory);
    }


    protected function configureGrid(BaseGrid $grid): void
    {
        $grid->setDataSource($this->facade->getSystemSelection());

        $grid->addColumnNumber(ExplorerNotificationRepository::COLUMN_ID, 'ID')
            ->setFitContent();

        $grid->addColumnText(ExplorerNotificationRepository::COLUMN_TYPE, 'Typ')
            ->setFitContent()
            ->setRenderer(function (ActiveRow $row): Html {
                $type = NotificationType::from($row[ExplorerNotificationRepository::COLUMN_TYPE]);
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

        $grid->addColumnText(ExplorerNotificationRepository::COLUMN_TITLE, 'Nadpis')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText(ExplorerNotificationRepository::COLUMN_MESSAGE, 'Zpráva')
            ->setRenderer(fn(ActiveRow $row) => Strings::truncate($row[ExplorerNotificationRepository::COLUMN_MESSAGE], 80));

        $grid->addColumnDateTime(ExplorerNotificationRepository::COLUMN_CREATED_AT, 'Vytvořeno')
            ->setSortable();

        $grid->setDefaultSort([ExplorerNotificationRepository::COLUMN_CREATED_AT => 'DESC']);
    }
}
