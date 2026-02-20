<?php

namespace App\Domain\Sign;

use App\Model\Security\SecurityUser;
use Nette\Security\AuthenticationException;

class SignService
{
    public function __construct(
        private readonly SecurityUser $securityUser,
    )
    {

    }


    /**
     * @throws AuthenticationException
     */
    public function signUserIn(string $login, string $password): void
    {
        $this->securityUser->login($login, $password);
    }
}
