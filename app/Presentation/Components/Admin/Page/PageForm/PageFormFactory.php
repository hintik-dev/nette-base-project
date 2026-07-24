<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\Page\PageForm;

interface PageFormFactory
{
    public function create(): PageForm;
}
