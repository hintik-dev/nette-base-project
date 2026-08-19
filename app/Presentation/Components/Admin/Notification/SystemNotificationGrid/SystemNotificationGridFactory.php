<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\Notification\SystemNotificationGrid;

interface SystemNotificationGridFactory
{
    public function create(): SystemNotificationGrid;
}
