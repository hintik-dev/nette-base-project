<?php declare(strict_types=1);
namespace App\Domain\User;

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


    /** @return User[] */
    public function getActiveUsers(): array
    {
        return $this->userRepository->getActiveUsers();
    }


    public function createUser(string $email, string $passwordHash, bool $active = true): User
    {
        return $this->userRepository->createUser($email, $passwordHash, $active);
    }


    public function userExistsByEmail(string $email, ?int $excludeId = null): bool
    {
        return $this->userRepository->userExistsByEmail($email, $excludeId);
    }


    public function updateUserPasswordHash(int $id, string $passwordHash): void
    {
        $this->userRepository->updateUserPasswordHash($id, $passwordHash);
    }


    public function updateUser(int $id, string $email, bool $active): void
    {
        $this->userRepository->updateUser($id, $email, $active);
    }


    public function setSuperadmin(int $id, bool $isSuperadmin): void
    {
        $this->userRepository->setSuperadmin($id, $isSuperadmin);
    }


    public function setActive(int $id, bool $active): void
    {
        $this->userRepository->setActive($id, $active);
    }


    public function updateUserLastLogin(int $id, ?DateTimeInterface $lastLogin = null): void
    {
        if ($lastLogin === null) {
            $lastLogin = new DateTimeImmutable();
        }

        $this->userRepository->updateUserLastLogin($id, $lastLogin);
    }
}
