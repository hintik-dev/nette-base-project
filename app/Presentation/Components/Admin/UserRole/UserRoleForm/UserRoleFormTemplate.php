<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\UserRole\UserRoleForm;

use App\Model\Latte\BaseTemplate;

final class UserRoleFormTemplate extends BaseTemplate
{
    public ?int $editId = null;

    public bool $isDefaultRole = false;
}
