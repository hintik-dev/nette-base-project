<?php declare(strict_types=1);
namespace App\Domain\User;

use App\Domain\UserRole\UserRole;
use DateTimeImmutable;
use DateTimeInterface;

readonly class UserService
{
    public function __construct(
        private ExplorerUserRepository $userRepository,
    ) {
    }


    public function getUserById(int $id): User
    {
        return $this->userRepository->getUserById($id);
    }


    public function getUserByEmail(string $email): User
    {
        return $this->userRepository->getUserByEmail($email);
    }


    public function createUser(string $email, string $passwordHash, UserRole $role, bool $active = true): User
    {
        return $this->userRepository->createUser($email, $passwordHash, $role, $active);
    }


    public function userExistsByEmail(string $email): bool
    {
        return $this->userRepository->userExistsByEmail($email);
    }


    public function updateUserPasswordHash(int $id, string $passwordHash): void
    {
        $this->userRepository->updateUserPasswordHash($id, $passwordHash);
    }


    public function updateUserLastLogin(int $id, ?DateTimeInterface $lastLogin = null): void
    {
        if ($lastLogin === null) {
            $lastLogin = new DateTimeImmutable();
        }

        $this->userRepository->updateUserLastLogin($id, $lastLogin);
    }
}
