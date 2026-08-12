<?php declare(strict_types=1);
namespace App\Domain\User;

use Nette\SmartObject;

class UserFormData
{
    use SmartObject;

    public const string PARAM_EMAIL = 'email';
    public const string PARAM_PASSWORD = 'password';
    public const string PARAM_ROLE_IDS = 'roleIds';
    public const string PARAM_ACTIVE = 'active';

    public string $email;

    public ?string $password = null;

    /**
     * ID přiřazených rolí. Prázdné pole = uživateli platí jen výchozí role.
     *
     * @var list<int>
     */
    public array $roleIds = [];

    public bool $active = true;
}
