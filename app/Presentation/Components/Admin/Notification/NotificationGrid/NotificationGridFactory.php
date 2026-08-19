<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\Notification\NotificationGrid;

interface NotificationGridFactory
{
    public function create(): NotificationGrid;
}
