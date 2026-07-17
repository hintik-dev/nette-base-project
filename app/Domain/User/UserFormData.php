<?php declare(strict_types=1);
namespace App\Domain\User;

use Nette\SmartObject;

class UserFormData
{
    use SmartObject;

    public const string PARAM_EMAIL = 'email';
    public const string PARAM_PASSWORD = 'password';
    public const string PARAM_ROLE = 'role';
    public const string PARAM_ACTIVE = 'active';

    public string $email;

    public ?string $password = null;

    public string $role;

    public bool $active = true;
}
