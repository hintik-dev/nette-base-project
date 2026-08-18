<?php declare(strict_types=1);
namespace App\Model\Security\Authenticator;

use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserService;
use App\Model\Security\IdentityFactory;
use App\Model\Security\Passwords;
use App\Model\Security\Permission\AdminPermission;
use App\Model\Security\Permission\PermissionEvaluator;
use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator;
use Nette\Security\IIdentity;

final class UserAuthenticator implements Authenticator
{
    public function __construct(
        private UserService $userService,
        private Passwords $passwords,
        private IdentityFactory $identityFactory,
        private PermissionEvaluator $permissionEvaluator,
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

        // Bez admin.access se uživatel nemá kam přihlásit. Evaluátor proto
        // pracuje s ID uživatele — v tuhle chvíli ještě žádná identita není.
        if (!$user->isSuperadmin && !$this->permissionEvaluator->isAllowed($user->id, AdminPermission::Access->getKey())) {
            throw new AuthenticationException('Access to administration has been revoked', self::NotApproved);
        }

        $this->userService->updateUserLastLogin($user->id);

        return $this->identityFactory->createIdentity($user);
    }
}
