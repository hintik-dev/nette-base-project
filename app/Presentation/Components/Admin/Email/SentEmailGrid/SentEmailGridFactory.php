<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\Email\SentEmailGrid;

interface SentEmailGridFactory
{
    public function create(): SentEmailGrid;
}
