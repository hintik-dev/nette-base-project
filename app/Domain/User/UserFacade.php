<?php declare(strict_types=1);
namespace App\Domain\User;

use App\Model\Security\Authorizator\InsufficientPrivilegesException;
use App\Model\Security\SecurityUser;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class UserFacade
{
    public function __construct(
        private readonly UserService $userService,
        private readonly ExplorerUserRepository $userRepository,
        private readonly SecurityUser $securityUser,
    ) {
    }


    /**
     * @throws InsufficientPrivilegesException
     * @return Selection<ActiveRow>
     */
    public function getAllUsersDataSource(): Selection
    {
        if (!$this->securityUser->isAllowed('user', 'list')) {
            throw new InsufficientPrivilegesException();
        }

        return $this->userRepository->getAllDataSource();
    }


    /**
     * @throws InsufficientPrivilegesException
     */
    public function getUserById(int $id): User
    {
        if (!$this->securityUser->isAllowed('user', 'detail')) {
            throw new InsufficientPrivilegesException();
        }

        return $this->userService->getUserById($id);
    }


    /**
     * @throws InsufficientPrivilegesException
     */
    public function getUserByEmail(string $email): User
    {
        if (!$this->securityUser->isAllowed('user', 'detail')) {
            throw new InsufficientPrivilegesException();
        }

        return $this->userService->getUserByEmail($email);
    }
}
