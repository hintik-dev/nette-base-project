<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\UserRole\UserRoleForm;

interface UserRoleFormFactory
{
    public function create(?int $editId): UserRoleForm;
}
