<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\UserRole\UserRolePermissionsForm;

interface UserRolePermissionsFormFactory
{
    public function create(int $roleId): UserRolePermissionsForm;
}
