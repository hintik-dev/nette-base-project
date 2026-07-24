<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\ApiUser\ApiUserGrid;

use App\Domain\ApiUser\ApiUserFacade;
use App\Domain\ApiUser\ExplorerApiUserRepository;
use App\Presentation\Components\Base\BaseGridComponent;
use App\Presentation\Control\DataGrid\BaseGrid;
use App\Presentation\Control\DataGrid\BaseGridFactory;
use Contributte\Datagrid\Column\Action\Confirmation\StringConfirmation;
use Nette\Utils\Html;

class ApiUserGrid extends BaseGridComponent
{
    public function __construct(
        private readonly ApiUserFacade $apiUserFacade,
        BaseGridFactory $baseGridFactory,
    ) {
        parent::__construct($baseGridFactory);
    }


    protected function configureGrid(BaseGrid $grid): void
    {
        $grid->setDataSource($this->apiUserFacade->getAllSelection());

        $grid->addColumnNumber(ExplorerApiUserRepository::COLUMN_ID, 'ID')
            ->setFitContent();

        $grid->addColumnText(ExplorerApiUserRepository::COLUMN_NAME, 'Název');

        $grid->addColumnText(ExplorerApiUserRepository::COLUMN_DESCRIPTION, 'Popis')
            ->setRenderer(function ($row): string {
                $description = $row[ExplorerApiUserRepository::COLUMN_DESCRIPTION];
                if ($description === null) {
                    return '—';
                }
                return mb_strlen($description) > 60
                    ? mb_substr($description, 0, 57) . '…'
                    : $description;
            });

        $grid->addColumnText(ExplorerApiUserRepository::COLUMN_IS_ACTIVE, 'Stav')
            ->setFitContent()
            ->setRenderer(function ($row): Html {
                $active = (bool) $row[ExplorerApiUserRepository::COLUMN_IS_ACTIVE];
                return Html::el('span')
                    ->setAttribute('class', $active ? 'badge bg-success' : 'badge bg-secondary')
                    ->setText($active ? 'Aktivní' : 'Neaktivní');
            });

        $grid->addColumnDateTime(ExplorerApiUserRepository::COLUMN_VALID_FROM, 'Platný od')
            ->setFormat('d.m.Y H:i')
            ->setRenderer(function ($row): string {
                $value = $row[ExplorerApiUserRepository::COLUMN_VALID_FROM];
                return $value !== null ? $value->format('d.m.Y H:i') : '—';
            });

        $grid->addColumnDateTime(ExplorerApiUserRepository::COLUMN_VALID_TO, 'Platný do')
            ->setFormat('d.m.Y H:i')
            ->setRenderer(function ($row): string {
                $value = $row[ExplorerApiUserRepository::COLUMN_VALID_TO];
                return $value !== null ? $value->format('d.m.Y H:i') : '—';
            });

        $grid->addColumnDateTime(ExplorerApiUserRepository::COLUMN_CREATED_AT, 'Vytvořeno')
            ->setFormat('d.m.Y H:i');

        $grid->addActionCallback('edit', 'Upravit', function (string $id): void {
            $this->presenter->redirect('edit', ['id' => (int) $id]);
        })->setIcon('pencil-fill')->setClass('btn btn-sm btn-outline-primary');

        $grid->addActionCallback('delete', 'Smazat', function (string $id): void {
            $this->presenter->redirect('delete!', ['id' => (int) $id]);
        })->setIcon('trash-fill')->setClass('btn btn-sm btn-outline-danger')
            ->setConfirmation(new StringConfirmation('Opravdu chcete smazat tohoto API uživatele?'));

        $grid->setDefaultSort([ExplorerApiUserRepository::COLUMN_ID => 'ASC']);
    }
}
