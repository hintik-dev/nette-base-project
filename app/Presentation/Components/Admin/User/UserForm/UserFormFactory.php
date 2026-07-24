<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\User\UserForm;

interface UserFormFactory
{
    public function create(?int $editId): UserForm;
}
