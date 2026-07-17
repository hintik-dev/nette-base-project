<?php declare(strict_types=1);
namespace App\Domain\User;

use App\Domain\UserRole\UserRole;
use App\Model\Security\Authorizator\InsufficientPrivilegesException;
use App\Model\Security\Passwords;
use App\Model\Security\SecurityUser;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class UserFacade
{
    public function __construct(
        private readonly UserService $userService,
        private readonly ExplorerUserRepository $userRepository,
        private readonly SecurityUser $securityUser,
        private readonly Passwords $passwords,
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


    /**
     * @throws InsufficientPrivilegesException
     */
    public function create(UserFormData $data): User
    {
        if (!$this->securityUser->isAllowed('user', 'create')) {
            throw new InsufficientPrivilegesException();
        }

        $passwordHash = $this->passwords->hash((string) $data->password);

        return $this->userService->createUser($data->email, $passwordHash, UserRole::from($data->role), $data->active);
    }


    /**
     * @throws InsufficientPrivilegesException
     */
    public function update(int $id, UserFormData $data): void
    {
        if (!$this->securityUser->isAllowed('user', 'edit')) {
            throw new InsufficientPrivilegesException();
        }

        $this->userService->updateUser($id, $data->email, UserRole::from($data->role), $data->active);

        if ($data->password !== null && $data->password !== '') {
            $this->userService->updateUserPasswordHash($id, $this->passwords->hash($data->password));
        }
    }


    /**
     * @throws InsufficientPrivilegesException
     * @throws CannotModifySelfException
     */
    public function setActive(int $id, bool $active): void
    {
        if (!$this->securityUser->isAllowed('user', 'edit')) {
            throw new InsufficientPrivilegesException();
        }

        if (!$active && $this->isCurrentUser($id)) {
            throw new CannotModifySelfException();
        }

        $this->userService->setActive($id, $active);
    }


    public function isCurrentUser(int $id): bool
    {
        return (int) $this->securityUser->getId() === $id;
    }


    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        return $this->userService->userExistsByEmail($email, $excludeId);
    }
}
