<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\Sign\PasswordResetForm;

interface PasswordResetFormFactory
{
    public function create(string $token): PasswordResetForm;
}
