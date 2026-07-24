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


    public function userExistsByEmail(string $email, ?int $excludeId = null): bool
    {
        return $this->userRepository->userExistsByEmail($email, $excludeId);
    }


    public function updateUserPasswordHash(int $id, string $passwordHash): void
    {
        $this->userRepository->updateUserPasswordHash($id, $passwordHash);
    }


    public function updateUser(int $id, string $email, UserRole $role, bool $active): void
    {
        $this->userRepository->updateUser($id, $email, $role, $active);
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
