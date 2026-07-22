<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\User\UserListGrid;

use App\Domain\User\ExplorerUserRepository;
use App\Domain\User\UserFacade;
use App\Domain\UserRole\UserRole;
use App\Presentation\Components\Base\BaseGridComponent;
use App\Presentation\Control\DataGrid\BaseGrid;
use App\Presentation\Control\DataGrid\BaseGridFactory;
use Contributte\Datagrid\Column\Action\Confirmation\StringConfirmation;

class UserListGrid extends BaseGridComponent
{
    public function __construct(
        private readonly UserFacade $userFacade,
        BaseGridFactory $baseGridFactory,
    ) {
        parent::__construct($baseGridFactory);
    }

    protected function configureGrid(BaseGrid $grid): void
    {
        $grid->setDataSource($this->userFacade->getAllUsersDataSource());

        $grid->addColumnNumber(ExplorerUserRepository::COLUMN_ID, 'ID')
            ->setFitContent();

        $grid->addColumnText(ExplorerUserRepository::COLUMN_EMAIL, 'E-mail')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText(ExplorerUserRepository::COLUMN_ROLE, 'Role')
            ->setRenderer(fn($row) => UserRole::from($row[ExplorerUserRepository::COLUMN_ROLE])->toLabel())
            ->setFitContent();

        $grid->addColumnStatus(ExplorerUserRepository::COLUMN_ACTIVE, 'Aktivní')
            ->addOption(true, 'Ano')
                ->setClass('badge text-bg-success')
            ->endOption()
            ->addOption(false, 'Ne')
                ->setClass('badge text-bg-secondary')
            ->endOption()
            ->setFitContent();

        $grid->addColumnDateTime(ExplorerUserRepository::COLUMN_LAST_LOGIN, 'Naposled přihlášen')
            ->setSortable()
            ->setFitContent();

        $grid->addActionCallback('edit', 'Upravit', function (string $id): void {
            $this->presenter->redirect('edit', ['id' => (int) $id]);
        })->setIcon('pencil-fill')->setClass('btn btn-sm btn-outline-primary');

        $grid->addActionCallback('deactivate', 'Deaktivovat', function (string $id): void {
            $this->presenter->redirect('setActive!', ['id' => (int) $id, 'active' => false]);
        })->setIcon('slash-circle')->setClass('btn btn-sm btn-outline-danger')
            ->setRenderCondition(fn($row) => (bool) $row[ExplorerUserRepository::COLUMN_ACTIVE]
                && !$this->userFacade->isCurrentUser((int) $row[ExplorerUserRepository::COLUMN_ID]))
            ->setConfirmation(new StringConfirmation('Opravdu chcete deaktivovat tohoto uživatele?'));

        $grid->addActionCallback('activate', 'Aktivovat', function (string $id): void {
            $this->presenter->redirect('setActive!', ['id' => (int) $id, 'active' => true]);
        })->setIcon('check-circle')->setClass('btn btn-sm btn-outline-success')
            ->setRenderCondition(fn($row) => !$row[ExplorerUserRepository::COLUMN_ACTIVE]);

        $grid->setDefaultSort([ExplorerUserRepository::COLUMN_ID => 'ASC']);
    }
}
