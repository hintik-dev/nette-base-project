<?php declare(strict_types=1);
namespace App\Domain\User;

use App\Domain\UserRole\AclPermission;
use App\Domain\UserRole\UserRoleFacade;
use App\Model\Security\Authorizator\InsufficientPrivilegesException;
use App\Model\Security\Passwords;
use App\Model\Security\Permission\PermissionDefinition;
use App\Model\Security\SecurityUser;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class UserFacade
{
    public function __construct(
        private readonly UserService $userService,
        private readonly ExplorerUserRepository $userRepository,
        private readonly UserRoleFacade $userRoleFacade,
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
        $this->assertAllowed(UserPermission::ListAll);

        return $this->userRepository->getAllDataSource();
    }


    /**
     * @throws InsufficientPrivilegesException
     */
    public function getUserById(int $id): User
    {
        $this->assertAllowed(UserPermission::Detail);

        return $this->userService->getUserById($id);
    }


    /**
     * @throws InsufficientPrivilegesException
     */
    public function getUserByEmail(string $email): User
    {
        $this->assertAllowed(UserPermission::Detail);

        return $this->userService->getUserByEmail($email);
    }


    /**
     * @throws InsufficientPrivilegesException
     */
    public function create(UserFormData $data): User
    {
        $this->assertAllowed(UserPermission::Create);

        $passwordHash = $this->passwords->hash((string) $data->password);

        $user = $this->userService->createUser($data->email, $passwordHash, $data->active);

        if ($this->canAssignRoles()) {
            $this->userRoleFacade->setRolesForUser($user->id, $data->roleIds);
        }

        return $user;
    }


    /**
     * @throws InsufficientPrivilegesException
     */
    public function update(int $id, UserFormData $data): void
    {
        $this->assertAllowed(UserPermission::Edit);

        $this->userService->updateUser($id, $data->email, $data->active);

        if ($data->password !== null && $data->password !== '') {
            $this->assertAllowed(UserPermission::ChangePassword);
            $this->userService->updateUserPasswordHash($id, $this->passwords->hash($data->password));
        }

        // Kdo nesmí přiřazovat role, uživatele upraví, ale jeho role nechá být.
        if ($this->canAssignRoles()) {
            $this->userRoleFacade->setRolesForUser($id, $data->roleIds);
        }
    }


    /**
     * @throws InsufficientPrivilegesException
     * @throws CannotModifySelfException
     */
    public function setActive(int $id, bool $active): void
    {
        $this->assertAllowed(UserPermission::Edit);

        if (!$active && $this->isCurrentUser($id)) {
            throw new CannotModifySelfException();
        }

        $this->userService->setActive($id, $active);
    }


    public function canAssignRoles(): bool
    {
        return $this->securityUser->isAllowed(AclPermission::RoleAssign);
    }


    public function isCurrentUser(int $id): bool
    {
        return $this->securityUser->getUserId() === $id;
    }


    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        return $this->userService->userExistsByEmail($email, $excludeId);
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
