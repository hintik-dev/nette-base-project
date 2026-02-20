<?php

namespace App\Domain\User;

use App\Model\Security\Authorizator\InsufficientPrivilegesException;
use App\Model\Security\SecurityUser;

class UserFacade
{
    public function __construct(
        private readonly UserService $userService,
        private readonly SecurityUser $securityUser,
    )
    {
    }


    /**
     * @throws InsufficientPrivilegesException
     */
    public function getUserById(int $id): User
    {
        if (!$this->securityUser->isAllowed('user', 'detail'))
        {
            throw new InsufficientPrivilegesException();
        }

        return $this->userService->getUserById($id);
    }


    /**
     * @throws InsufficientPrivilegesException
     */
    public function getUserByEmail(string $email): User
    {
        if (!$this->securityUser->isAllowed('user', 'detail'))
        {
            throw new InsufficientPrivilegesException();
        }

        return $this->userService->getUserByEmail($email);
    }
}
