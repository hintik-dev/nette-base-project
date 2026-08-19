<?php declare(strict_types=1);
namespace App\Domain\PasswordReset;

use Nette\SmartObject;

class PasswordResetFormData
{
    use SmartObject;

    public const string PARAM_PASSWORD = 'password';
    public const string PARAM_PASSWORD_CONFIRM = 'passwordConfirm';

    public string $password;

    public string $passwordConfirm;
}
