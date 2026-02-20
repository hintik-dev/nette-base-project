<?php

namespace App\Domain\Sign;

use App\Model\Security\SecurityUser;
use Nette\InvalidStateException;
use Nette\Security\AuthenticationException;

class SignFacade
{
    public function __construct(
        private readonly SecurityUser $securityUser,
        private readonly SignService $signService,
    )
    {
    }

    /**
     * @throws AuthenticationException
     */
    public function signUserIn(SignInFormData $signFormData): void
    {
        if($this->securityUser->isLoggedIn())
        {
            throw new InvalidStateException('User is already logged in.');
        }

        $this->signService->signUserIn($signFormData->login, $signFormData->password);
    }
}
