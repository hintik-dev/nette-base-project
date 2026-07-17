<?php declare(strict_types=1);
namespace App\Domain\UserSession;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class UserSessionFacade
{
    public function __construct(
        private readonly ExplorerUserSessionRepository $repository,
    ) {
    }


    /** @return Selection<ActiveRow> */
    public function getAllSelection(): Selection
    {
        return $this->repository->getAllSelection();
    }


    public function terminate(int $id): void
    {
        $session = $this->repository->findById($id);
        if ($session === null || !$session->isActive()) {
            return;
        }

        $this->repository->markAsLoggedOut($id, LogoutReason::Forced);
    }
}
