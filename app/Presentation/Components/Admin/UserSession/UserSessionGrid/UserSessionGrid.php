<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\UserSession\UserSessionGrid;

use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserService;
use App\Domain\UserSession\ExplorerUserSessionRepository;
use App\Domain\UserSession\LogoutReason;
use App\Domain\UserSession\UserSessionFacade;
use App\Presentation\Components\Base\BaseGridComponent;
use App\Presentation\Control\DataGrid\BaseGrid;
use App\Presentation\Control\DataGrid\BaseGridFactory;
use Contributte\Datagrid\Column\Action\Confirmation\StringConfirmation;
use Nette\Utils\Html;

class UserSessionGrid extends BaseGridComponent
{
    public function __construct(
        private readonly UserSessionFacade $facade,
        private readonly UserService $userService,
        BaseGridFactory $baseGridFactory,
    ) {
        parent::__construct($baseGridFactory);
    }


    protected function configureGrid(BaseGrid $grid): void
    {
        $grid->setDataSource($this->facade->getAllSelection());

        $grid->addColumnNumber(ExplorerUserSessionRepository::COLUMN_ID, 'ID')
            ->setFitContent();

        $grid->addColumnText(ExplorerUserSessionRepository::COLUMN_USER_ID, 'Uživatel')
            ->addCellAttributes(['style' => 'min-width: 150px'])
            ->setRenderer(function ($row): string {
                try {
                    $user = $this->userService->getUserById(
                        (int) $row[ExplorerUserSessionRepository::COLUMN_USER_ID],
                    );
                    return $user->email;
                } catch (UserNotFoundException) {
                    return '#' . $row[ExplorerUserSessionRepository::COLUMN_USER_ID];
                }
            });

        $grid->addColumnText(ExplorerUserSessionRepository::COLUMN_IP_ADDRESS, 'IP adresa')
            ->setFitContent();

        $grid->addColumnText(ExplorerUserSessionRepository::COLUMN_USER_AGENT, 'User Agent')
            ->setRenderer(function ($row): string {
                $ua = $row[ExplorerUserSessionRepository::COLUMN_USER_AGENT];
                if ($ua === null) {
                    return '—';
                }
                return mb_strlen($ua) > 60 ? mb_substr($ua, 0, 57) . '…' : $ua;
            });

        $grid->addColumnDateTime(ExplorerUserSessionRepository::COLUMN_CREATED_AT, 'Vytvořeno')
            ->setFormat('d.m.Y H:i:s');

        $grid->addFilterDateTimeRangeFromDatetime(ExplorerUserSessionRepository::COLUMN_CREATED_AT, 'Vytvořeno');

        $grid->addColumnDateTime(ExplorerUserSessionRepository::COLUMN_LAST_ACTIVITY_AT, 'Poslední aktivita')
            ->setFormat('d.m.Y H:i:s');

        $grid->addFilterDateTimeRangeFromDatetime(ExplorerUserSessionRepository::COLUMN_LAST_ACTIVITY_AT, 'Poslední aktivita');

        $grid->addColumnText(ExplorerUserSessionRepository::COLUMN_LOGOUT_REASON, 'Stav')
            ->setFitContent()
            ->setRenderer(function ($row): Html {
                $reason = $row[ExplorerUserSessionRepository::COLUMN_LOGOUT_REASON];

                if ($reason === null) {
                    return Html::el('span')
                        ->setAttribute('class', 'badge bg-success')
                        ->setText('Aktivní');
                }

                $label = match (LogoutReason::from($reason)) {
                    LogoutReason::Manual        => 'Odhlášen',
                    LogoutReason::Inactivity    => 'Nečinnost',
                    LogoutReason::Forced        => 'Vynuceno',
                    LogoutReason::SingleSession => 'Nová session',
                    LogoutReason::AccessRevoked => 'Odebrán přístup',
                };

                return Html::el('span')
                    ->setAttribute('class', 'badge bg-secondary')
                    ->setText($label);
            });

        $grid->addActionCallback('terminate', 'Ukončit session', function (string $id): void {
            $this->facade->terminate((int) $id);
            $this->flashSuccess('Session byla ukončena.');
            $this->presenter->redirect('this');
        })->setIcon('x-circle-fill')
            ->setClass('btn btn-sm btn-outline-danger')
            ->setRenderCondition(fn($row) => $row[ExplorerUserSessionRepository::COLUMN_LOGOUT_REASON] === null)
            ->setConfirmation(new StringConfirmation('Opravdu chcete ukončit tuto session?'));

        $grid->setDefaultSort([ExplorerUserSessionRepository::COLUMN_ID => 'DESC']);
    }
}
