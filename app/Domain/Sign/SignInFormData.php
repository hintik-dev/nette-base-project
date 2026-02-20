<?php

namespace App\Domain\Sign;

use Nette\SmartObject;

class SignInFormData
{
    use SmartObject;

    public const string PARAM_LOGIN = 'login';
    public const string PARAM_PASSWORD = 'password';

    public string $login;

    public string $password;
}
