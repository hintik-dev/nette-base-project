<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\UserRole\UserRoleGrid;

use App\Domain\UserRole\ExplorerUserRoleRepository;
use App\Domain\UserRole\UserRole;
use App\Domain\UserRole\UserRoleFacade;
use App\Presentation\Components\Base\BaseGridComponent;
use App\Presentation\Control\DataGrid\BaseGrid;
use App\Presentation\Control\DataGrid\BaseGridFactory;
use Contributte\Datagrid\Column\Action\Confirmation\StringConfirmation;
use Nette\Utils\Html;

class UserRoleGrid extends BaseGridComponent
{
    public function __construct(
        private readonly UserRoleFacade $userRoleFacade,
        BaseGridFactory $baseGridFactory,
    ) {
        parent::__construct($baseGridFactory);
    }


    protected function configureGrid(BaseGrid $grid): void
    {
        $grid->setDataSource($this->userRoleFacade->getAllRolesDataSource());

        $grid->addColumnNumber(ExplorerUserRoleRepository::COLUMN_PRIORITY, 'Priorita')
            ->setSortable()
            ->setFitContent();

        $grid->addColumnText(ExplorerUserRoleRepository::COLUMN_NAME, 'Název')
            ->setRenderer(function ($row): Html {
                $name = Html::el('span')->setText($row[ExplorerUserRoleRepository::COLUMN_NAME]);

                if ((int) $row[ExplorerUserRoleRepository::COLUMN_PRIORITY] !== UserRole::DEFAULT_PRIORITY) {
                    return $name;
                }

                return Html::el('span')
                    ->addHtml($name)
                    ->addHtml(Html::el('span')
                        ->setAttribute('class', 'badge bg-secondary ms-2')
                        ->setText('výchozí'));
            });

        $grid->addColumnText(ExplorerUserRoleRepository::COLUMN_CODE, 'Kód')
            ->setRenderer(fn ($row): Html => Html::el('code')
                ->setText($row[ExplorerUserRoleRepository::COLUMN_CODE]))
            ->setFitContent();

        $grid->addColumnText(ExplorerUserRoleRepository::COLUMN_DESCRIPTION, 'Popis')
            ->setRenderer(fn ($row): string => $row[ExplorerUserRoleRepository::COLUMN_DESCRIPTION] ?? '—');

        $grid->addColumnText('users', 'Uživatelů')
            ->setRenderer(fn ($row): string => (string) $this->userRoleFacade->countUsersWithRole(
                (int) $row[ExplorerUserRoleRepository::COLUMN_ID],
            ))
            ->setFitContent();

        $grid->addActionCallback('edit', 'Upravit', function (string $id): void {
            $this->presenter->redirect('edit', ['id' => (int) $id]);
        })->setIcon('pencil-fill')->setClass('btn btn-sm btn-outline-primary');

        // Výchozí a systémovou roli nelze smazat, tlačítko se u nich nezobrazí.
        $grid->addActionCallback('delete', 'Smazat', function (string $id): void {
            $this->presenter->redirect('delete!', ['id' => (int) $id]);
        })->setIcon('trash-fill')->setClass('btn btn-sm btn-outline-danger')
            ->setRenderCondition(fn ($row): bool => (int) $row[ExplorerUserRoleRepository::COLUMN_PRIORITY] !== UserRole::DEFAULT_PRIORITY
                && !(bool) $row[ExplorerUserRoleRepository::COLUMN_IS_SYSTEM])
            ->setConfirmation(new StringConfirmation('Opravdu chcete smazat tuto roli? Uživatelé o její oprávnění okamžitě přijdou.'));

        $grid->setDefaultSort([ExplorerUserRoleRepository::COLUMN_PRIORITY => 'ASC']);
    }
}
