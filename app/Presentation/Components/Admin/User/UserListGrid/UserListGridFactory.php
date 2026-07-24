<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\User\UserListGrid;

interface UserListGridFactory
{
    public function create(): UserListGrid;
}
