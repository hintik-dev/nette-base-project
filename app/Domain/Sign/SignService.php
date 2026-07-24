<?php declare(strict_types=1);
namespace App\Domain\Sign;

use App\Model\Security\SecurityUser;
use Nette\Security\AuthenticationException;

class SignService
{
    public function __construct(
        private readonly SecurityUser $securityUser,
    ) {
    }


    /**
     * @throws AuthenticationException
     */
    public function signUserIn(string $login, string $password, bool $remember = false): void
    {
        $this->securityUser->login($login, $password);

        // remember = true  → žádný inactivity timeout, session trvá do odhlášení
        // remember = false → odhlášení po 12 hodinách nečinnosti
        $this->securityUser->setExpiration($remember ? null : '12 hours');
    }
}
