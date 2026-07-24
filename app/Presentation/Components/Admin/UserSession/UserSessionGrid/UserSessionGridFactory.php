<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\UserSession\UserSessionGrid;

interface UserSessionGridFactory
{
    public function create(): UserSessionGrid;
}
