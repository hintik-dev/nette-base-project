<?php declare(strict_types=1);
namespace App\Presentation\Modules\Admin\UserRole;

use App\Domain\UserRole\UserRole;
use App\Model\Latte\BaseTemplate;

final class UserRoleEditTemplate extends BaseTemplate
{
    public UserRole $userRole;
}
