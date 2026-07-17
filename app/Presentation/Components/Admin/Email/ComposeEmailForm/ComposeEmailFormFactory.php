<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\Email\ComposeEmailForm;

interface ComposeEmailFormFactory
{
    public function create(): ComposeEmailForm;
}
