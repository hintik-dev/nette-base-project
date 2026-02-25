<?php declare(strict_types=1);
namespace App\Model\Security\Authenticator;

use App\Domain\User\User;
use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserService;
use App\Model\Security\Identity;
use App\Model\Security\Passwords;
use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator;
use Nette\Security\IIdentity;

final class UserAuthenticator implements Authenticator
{
    public function __construct(
        private UserService $userService,
        private Passwords $passwords
    ) {
    }


    /**
     * @throws AuthenticationException
     */
    public function authenticate(string $login, string $password): IIdentity
    {
        $user = null;
        try {
            $user = $this->userService->getUserByEmail($login);
        } catch (UserNotFoundException) {
            throw new AuthenticationException('The username is incorrect.', self::IdentityNotFound);
        }

        if (!$this->passwords->verify($password, $user->passwordHash)) {
            throw new AuthenticationException('The password is incorrect.', self::InvalidCredential);
        }

        if (!$user->active) {
            throw new AuthenticationException('Account is blocked', self::NotApproved);
        }

        $this->userService->updateUserLastLogin($user->id);

        return $this->createIdentity($user);
    }


    protected function createIdentity(User $user): IIdentity
    {
        return new Identity($user->id, [$user->role], [
            'email' => $user->email,
        ]);
    }
}
