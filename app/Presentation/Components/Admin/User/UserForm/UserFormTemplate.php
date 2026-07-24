<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\User\UserForm;

use App\Model\Latte\BaseTemplate;

final class UserFormTemplate extends BaseTemplate
{
    public ?int $editId = null;

    public string $backLink;
}
