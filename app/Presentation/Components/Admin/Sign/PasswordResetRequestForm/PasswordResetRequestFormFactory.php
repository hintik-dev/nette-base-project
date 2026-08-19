<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\Sign\PasswordResetRequestForm;

interface PasswordResetRequestFormFactory
{
    public function create(): PasswordResetRequestForm;
}
