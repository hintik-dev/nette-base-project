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


    public function updateUserLastLogin(int $id, ?DateTimeInterface $lastLogin = null): void
    {
        if ($lastLogin === null) {
            $lastLogin = new DateTimeImmutable();
        }

        $this->userRepository->updateUserLastLogin($id, $lastLogin);
    }
}
