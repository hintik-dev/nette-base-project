<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\User\UserForm;

use App\Model\Latte\BaseTemplate;

final class UserFormTemplate extends BaseTemplate
{
    public ?int $editId = null;

    public string $backLink = '';

    /** Pole rolí se vykresluje jen tomu, kdo je smí přiřazovat. */
    public bool $canAssignRoles = false;

    /** Superadmin obchází ACL — role u něj nic neovlivní, je potřeba to říct. */
    public bool $isSuperadmin = false;
}
