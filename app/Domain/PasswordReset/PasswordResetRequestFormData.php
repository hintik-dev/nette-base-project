<?php declare(strict_types=1);
namespace App\Domain\PasswordReset;

use Nette\SmartObject;

class PasswordResetRequestFormData
{
    use SmartObject;

    public const string PARAM_EMAIL = 'email';

    public string $email;
}
