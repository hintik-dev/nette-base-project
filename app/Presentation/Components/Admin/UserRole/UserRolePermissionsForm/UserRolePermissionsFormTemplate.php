<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\UserRole\UserRolePermissionsForm;

use App\Model\Latte\BaseTemplate;

final class UserRolePermissionsFormTemplate extends BaseTemplate
{
    /** @var array<string, list<PermissionMatrixRow>> */
    public array $groups = [];

    /** @var list<string> */
    public array $orphanKeys = [];

    public string $roleName = '';

    public bool $isDefaultRole = false;
}
