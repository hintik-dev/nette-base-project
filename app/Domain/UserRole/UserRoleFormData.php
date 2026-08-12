<?php declare(strict_types=1);
namespace App\Domain\UserRole;

use Nette\SmartObject;

class UserRoleFormData
{
    use SmartObject;

    public const string PARAM_CODE = 'code';
    public const string PARAM_NAME = 'name';
    public const string PARAM_DESCRIPTION = 'description';
    public const string PARAM_PRIORITY = 'priority';

    public string $code;

    public string $name;

    public ?string $description = null;

    public int $priority;
}
