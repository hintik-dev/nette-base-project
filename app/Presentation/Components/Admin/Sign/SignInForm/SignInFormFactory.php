<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\Sign\SignInForm;

interface SignInFormFactory
{
    /**
     * @param array<callable(): void> $onLogIn
     * @return SignInForm
     */
    public function create(array $onLogIn = []): SignInForm;
}
