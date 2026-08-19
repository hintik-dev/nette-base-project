<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\Notification\NotificationsBell;

interface NotificationsBellFactory
{
    public function create(): NotificationsBell;
}
