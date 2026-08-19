<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\Email\MailQueueGrid;

interface MailQueueGridFactory
{
    public function create(): MailQueueGrid;
}
