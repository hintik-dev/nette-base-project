<?php

namespace App\Domain\User;

use App\Domain\DataSource\DataSourceInterface;
use App\Model\Security\Authorizator\InsufficientPrivilegesException;
use App\Model\Security\Passwords;
use App\Model\Security\SecurityUser;
use Cake\Datasource\EntityInterface;
use DateTimeImmutable;
use DateTimeInterface;

readonly class UserService
{

    public function __construct(
        private ExplorerUserRepository $userRepository,
    )
    {

    }


    public function getUserById(int $id): User
    {
        return $this->userRepository->getUserById($id);
    }


    public function getUserByEmail(string $email): User
    {
        return $this->userRepository->getUserByEmail($email);
    }


    public function updateUserLastLogin(int $id, ?DateTimeInterface $lastLogin = null): void
    {
        if($lastLogin === null)
        {
            $lastLogin = new DateTimeImmutable();
        }

        $this->userRepository->updateUserLastLogin($id, $lastLogin);
    }
}
