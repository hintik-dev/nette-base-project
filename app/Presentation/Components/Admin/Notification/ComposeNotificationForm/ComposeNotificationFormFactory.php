<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\Notification\ComposeNotificationForm;

interface ComposeNotificationFormFactory
{
    public function create(): ComposeNotificationForm;
}
