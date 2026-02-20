<?php

namespace App\Presentation\Components\Admin\Sign\SignInForm;

interface SignInFormFactory
{
    /**
     * @param array<callable(): void> $onLogIn
     * @return SignInForm
     */
    public function create(array $onLogIn = []): SignInForm;
}
