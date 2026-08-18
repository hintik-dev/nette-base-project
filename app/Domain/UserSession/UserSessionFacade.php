<?php declare(strict_types=1);
namespace App\Domain\UserSession;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use App\Model\Security\Authorizator\InsufficientPrivilegesException;
use App\Model\Security\Permission\PermissionDefinition;
use App\Model\Security\SecurityUser;

class UserSessionFacade
{
    public function __construct(
        private readonly ExplorerUserSessionRepository $repository,
        private readonly SecurityUser $securityUser,
    ) {
    }


    /** @return Selection<ActiveRow> */
    public function getAllSelection(): Selection
    {
        $this->assertAllowed(UserSessionPermission::ListAll);

        return $this->repository->getAllSelection();
    }


    public function terminate(int $id): void
    {
        $this->assertAllowed(UserSessionPermission::Terminate);

        $session = $this->repository->findById($id);
        if ($session === null || !$session->isActive()) {
            return;
        }

        $this->repository->markAsLoggedOut($id, LogoutReason::Forced);
    }


    /**
     * @throws InsufficientPrivilegesException
     */
    private function assertAllowed(PermissionDefinition $permission): void
    {
        if (!$this->securityUser->isAllowed($permission)) {
            throw new InsufficientPrivilegesException();
        }
    }
}
