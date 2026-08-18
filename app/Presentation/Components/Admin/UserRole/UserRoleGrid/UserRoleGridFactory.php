<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\UserRole\UserRoleGrid;

interface UserRoleGridFactory
{
    public function create(): UserRoleGrid;
}
