<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\ApiUser\ApiUserForm;

interface ApiUserFormFactory
{
    public function create(?int $editId): ApiUserForm;
}
